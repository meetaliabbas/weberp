<?php

include('includes/session.php');
global $RootPath, $Theme;

$Title = _('Fixed Asset Locations');
$ViewTopic = 'FixedAssets';
$BookMark = 'AssetLocations';
include('includes/header.php');

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title.'
	</p>';

if (isset($_POST['submit']) AND !isset($_POST['delete'])) {
	$InputError=0;
	if (!isset($_POST['LocationID']) OR mb_strlen($_POST['LocationID'])<1) {
		prnMsg(_('You must enter at least one character in the location ID'),'error');
		$InputError=1;
	}
	if (!isset($_POST['LocationDescription']) OR mb_strlen($_POST['LocationDescription'])<1) {
		prnMsg(_('You must enter at least one character in the location description'),'error');
		$InputError=1;
	}
	if ($InputError==0) {
		$SQL="INSERT INTO fixedassetlocations
				VALUES ('".$_POST['LocationID']."',
						'".$_POST['LocationDescription']."',
						'".$_POST['ParentLocationID']."')";
		$Result = DB_query($SQL);
	}
}
if (isset($_GET['SelectedLocation'])) {
	$SQL="SELECT * FROM fixedassetlocations
		WHERE locationid='".$_GET['SelectedLocation']."'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$LocationID = $MyRow['locationid'];
	$LocationDescription = $MyRow['locationdescription'];
	$ParentLocationID = $MyRow['parentlocationid'];

} else {
	$LocationID = '';
	$LocationDescription = '';
}

//Attempting to update fields

if (isset($_POST['update']) and !isset($_POST['delete'])) {
		$InputError=0;
		if (!isset($_POST['LocationDescription']) or mb_strlen($_POST['LocationDescription'])<1) {
			prnMsg(_('You must enter at least one character in the location description'),'error');
			$InputError=1;
		}
		if ($InputError==0) {
			 $SQL="UPDATE fixedassetlocations
					SET locationdescription='" . $_POST['LocationDescription'] . "',
						parentlocationid='" . $_POST['ParentLocationID'] . "'
					WHERE locationid ='" . $_POST['LocationID'] . "'";

			 $Result = DB_query($SQL);
			 echo '<meta http-equiv="Refresh" content="0; url="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8').'">';
		}
} else {
	// if you are not updating then you want to delete but lets be sure first.
	if (isset($_POST['delete']))  {
		$InputError=0;

		$SQL="SELECT COUNT(locationid) FROM fixedassetlocations WHERE parentlocationid='" . $_POST['LocationID']."'";
		$Result = DB_query($SQL);
		$MyRow=DB_fetch_row($Result);
		if ($MyRow[0]>0) {
			prnMsg(_('This location has child locations so cannot be removed'), 'warning');
			$InputError=1;
		}
		$SQL="SELECT COUNT(assetid) FROM fixedassets WHERE assetlocation='" . $_POST['LocationID']."'";
		$Result = DB_query($SQL);
		$MyRow=DB_fetch_row($Result);
		if ($MyRow[0]>0) {
			prnMsg(_('You have assets in this location so it cannot be removed'), 'warn');
			$InputError=1;
		}
		if ($InputError==0) {
			$SQL = "DELETE FROM fixedassetlocations WHERE locationid = '".$_POST['LocationID']."'";
			$Result = DB_query($SQL);
			prnMsg(_('The location has been deleted successfully'), 'success');
		}
	}
}

$SQL='SELECT * FROM fixedassetlocations';
$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0) {
	echo '<table class="selection">
		<thead>
		<tr>
			<th class="SortedColumn">' . _('Location ID') . '</th>
			<th class="SortedColumn">' . _('Location Description') . '</th>
			<th class="SortedColumn">' . _('Parent Location') . '</th>
			</tr>
		</thead>
		<tbody>';

	while ($MyRow=DB_fetch_array($Result)) {
		echo '<tr>
				<td>' . $MyRow['locationid'] . '</td>
				<td>' . $MyRow['locationdescription'] . '</td>';
		if ($MyRow['parentlocationid'] != '') {
			$ParentSql="SELECT locationdescription FROM fixedassetlocations WHERE locationid='".$MyRow['parentlocationid']."'";
			$ParentResult = DB_query($ParentSql);
			$ParentRow=DB_fetch_array($ParentResult);
			echo '<td>' . $ParentRow['locationdescription'] . '</td>';
		} else {
			echo '<td>', _('No Parent Location'), '</td>';
		}
		echo '<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedLocation=', urlencode($MyRow['locationid']), '">', _('Edit'), '</a></td></tr>';
	}

	echo '</tbody></table>';
}

	echo '<form id="LocationForm" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>';
if (isset($_GET['SelectedLocation'])) {
	echo '<legend>', _('Edit Asset Location'), '</legend>';

	echo '<field>
			<label for="LocationID">' . _('Location ID') . '</label>
			<input type="hidden" name="LocationID" value="'.$LocationID.'" />
			<fieldtext>' . $LocationID . '</fieldtext>
		</field>';
} else {
	echo '<legend>', _('Create Asset Location'), '</legend>
			<field>
				<label for="LocationID">' . _('Location ID') . '</label>
				<input type="text" name="LocationID" required="required" title="" data-type="no-illegal-chars" size="6" value="'.$LocationID.'" />
				<fieldhelp>' . _('Enter the location code of the fixed asset location. Up to six alpha-numeric characters') . '</fieldhelp>
		</field>';
}

echo '<field>
		<label for="LocationDescription">' . _('Location Description') . '</label>
		<input type="text" name="LocationDescription" required="required" title="" size="20" value="'.$LocationDescription.'" />
		<fieldhelp>' . _('Enter the fixed asset location description. Up to 20 characters') . '</fieldhelp>
	</field>';

echo '<field>
		<label for="ParentLocationID">' . _('Parent Location') . '</label>
		<select name="ParentLocationID">';

$SQL="SELECT locationid, locationdescription FROM fixedassetlocations";
$Result = DB_query($SQL);

echo '<option value=""></option>';
while ($MyRow=DB_fetch_array($Result)) {
	if (isset($_GET['SelectedLocation']) and $_GET['SelectedLocation'] != $MyRow['locationid']) {
		if ($MyRow['locationid']==$ParentLocationID) {
			echo '<option selected="selected" value="' . $MyRow['locationid'] . '">' . $MyRow['locationdescription'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['locationid'] . '">' . $MyRow['locationdescription'] . '</option>';
		}
	}
}
echo '</select>
	</field>
</fieldset>';

echo '<div class="centre">';
if (isset($_GET['SelectedLocation'])) {
	echo '<input type="submit" name="update" value="' . _('Update Information') . '" />
		<br /><br />
		<input type="reset" name="delete" value="' . _('Delete This Location') . '" />';
} else {
	echo '<input type="submit" name="submit" value="' . _('Enter Information') . '" />';
}
echo '</div>
	</form>';

include('includes/footer.php');
