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

if (isActionAccessible($guid, $connection2, '/modules/Committees/committees_manage.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $page->breadcrumbs->add(__m('Manage Committees'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $committeeGateway = $container->get(CommitteeGateway::class);

    // QUERY
    $criteria = $committeeGateway->newQueryCriteria()
        ->sortBy('name', 'ASC')
        ->fromPOST();

    $committees = $committeeGateway->queryCommittees($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('committees', $criteria);
    $table->setTitle(__('View'));

    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/Committees/committees_manage_add.php')
        ->displayLabel();

    $table->modifyRows(function ($committee, $row) {
        if ($committee['active'] != 'Y') $row->addClass('error');
        return $row;
    });

    $table->addColumn('name', __('Name'))
        ->format(function ($committee) {
            $url = './index.php?q=/modules/Committees/committee.php&committeesCommitteeID='.$committee['committeesCommitteeID'];
            return Format::link($url, $committee['name']);
        });
    $table->addColumn('members', __('Members'))->width('10%');
    $table->addColumn('active', __('Active'))->format(Format::using('yesNo', 'active'));

    // ACTIONS
    $table->addActionColumn()
        ->addParam('committeesCommitteeID')
        ->format(function ($committee, $actions) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Committees/committees_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Committees/committees_manage_delete.php');
        });

    echo $table->render($committees);
}
