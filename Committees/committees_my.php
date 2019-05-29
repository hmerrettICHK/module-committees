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

use Gibbon\Tables\DataTable;
use Gibbon\Module\Committees\Domain\CommitteeGateway;
use Gibbon\Services\Format;
use Gibbon\Domain\School\SchoolYearGateway;

if (isActionAccessible($guid, $connection2, '/modules/Committees/committees_my.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $page->breadcrumbs->add(__m('My Committees'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $committeeGateway = $container->get(CommitteeGateway::class);

    // QUERY
    $criteria = $committeeGateway->newQueryCriteria()
        ->sortBy('name', 'ASC')
        ->fromPOST();

    $committees = $committeeGateway->queryCommitteesByMember($criteria, $gibbon->session->get('gibbonSchoolYearID'), $gibbon->session->get('gibbonPersonID'));
    $canSignup = isActionAccessible($guid, $connection2, '/modules/Committees/committee_signup.php');

    // DATA TABLE
    $table = DataTable::createPaginated('committees', $criteria);
    $table->setTitle(__m('My Committees'));
    $table->addMetaData('blankSlate', __m('You are not currently a member of any committees.'));

    $table->modifyRows(function ($committee, $row) {
        if ($committee['active'] != 'Y') $row->addClass('error');
        return $row;
    });

    $table->addColumn('name', __('Name'))
        ->format(function ($committee) {
            $url = './index.php?q=/modules/Committees/committee.php&committeesCommitteeID='.$committee['committeesCommitteeID'];
            return Format::link($url, $committee['name']);
        });

    // ACTIONS
    $table->addActionColumn()
        ->addParam('committeesCommitteeID')
        ->format(function ($committee, $actions) use ($canSignup) {
            $actions->addAction('view', __('View'))
                    ->setURL('/modules/Committees/committee.php');
            
            if ($canSignup) {
                $actions->addAction('leave', __('Leave Committee'))
                        ->setIcon('iconCross')
                        ->setURL('/modules/Committees/committee_leave.php');
            }
        });

    echo $table->render($committees);
}
