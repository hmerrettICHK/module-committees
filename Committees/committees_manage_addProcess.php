<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

use Gibbon\Module\Committees\Domain\CommitteeGateway;

require_once '../../gibbon.php';

$search = $_GET['search'] ?? '';
$gibbonSchoolYearID = $_REQUEST['gibbonSchoolYearID'] ?? $gibbon->session->get('gibbonSchoolYearID');

$URL = $gibbon->session->get('absoluteURL').'/index.php?q=/modules/Committees/committees_manage_add.php&gibbonSchoolYearID='.$gibbonSchoolYearID.'&search='.$search;

if (isActionAccessible($guid, $connection2, '/modules/Committees/committees_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    // Proceed!
    $committeeGateway = $container->get(CommitteeGateway::class);

    $data = [
        'gibbonSchoolYearID' => $_POST['gibbonSchoolYearID'] ?? '',
        'name'               => $_POST['name'] ?? '',
        'active'             => $_POST['active'] ?? '',
        'signup'             => $_POST['signup'] ?? '',
        'description'        => $_POST['description'] ?? '',
        'logo'               => null,
    ];

    // Validate the required values are present
    if (empty($data['gibbonSchoolYearID']) || empty($data['name']) || empty($data['active'])) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    // Validate that this record is unique
    if (!$committeeGateway->unique($data, ['name', 'gibbonSchoolYearID'])) {
        $URL .= '&return=error7';
        header("Location: {$URL}");
        exit;
    }

    // Move attached file, if there is one
    if (!empty($_FILES['file']['tmp_name'])) {
        $file = $_FILES['file'] ?? null;
        
        // Upload the file, return the /uploads relative path
        $fileUploader = new Gibbon\FileUploader($pdo, $gibbon->session);
        $data['logo'] = $fileUploader->uploadFromPost($file, $data['name']);
    }

    // Create the record
    $committeesCommitteeID = $committeeGateway->insert($data);

    $URL .= !$committeesCommitteeID
        ? "&return=error2"
        : "&return=success0";

    header("Location: {$URL}&editID=$committeesCommitteeID");
}
