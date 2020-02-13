<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 3/01/2020
 * Time: 16:31
 */

namespace Kookaburra\RollGroups\Pagination;

use App\Manager\Entity\PaginationAction;
use App\Manager\Entity\PaginationColumn;
use App\Manager\Entity\PaginationRow;
use App\Manager\ReactPaginationInterface;
use App\Manager\AbstractPaginationManager;
use App\Util\TranslationsHelper;
use Kookaburra\UserAdmin\Entity\Person;
use Kookaburra\UserAdmin\Manager\SecurityUser;
use Kookaburra\UserAdmin\Util\UserHelper;

/**
 * Class RollGroupListPagination
 * @package Kookaburra\RollGroups\Pagination
 */
class RollGroupListPagination  extends AbstractPaginationManager
{
    /**
     * execute
     * @return ReactPaginationInterface
     */
    public function execute(): ReactPaginationInterface
    {
        TranslationsHelper::setDomain('RollGroups');
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('Name')
            ->setContentKey(['name'])
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Tutors')
            ->setContentKey('tutors')
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Room')
            ->setContentKey('location')
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        if (UserHelper::isStaff($this->getCurrentUser()->getPerson())) {
            $column = new PaginationColumn();
            $column->setLabel('Students')
                ->setContentKey('students')
                ->setClass('column relative pr-4 cursor-pointer widthAuto text-centre');
            $row->addColumn($column);
        }

        $column = new PaginationColumn();
        $column->setLabel('Website')
            ->setContentKey('website')
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $action = new PaginationAction();
        $action->setTitle('View')
            ->setAClass('')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-search-plus fa-fw fa-1-5x text-gray-700')
            ->setRoute('roll_groups__detail')
            ->setRouteParams(['rollGroup' => 'id']);
        $row->addAction($action);

        $this->setRow($row);

        return $this;
    }

    /**
     * @var SecurityUser
     */
    private $currentUser;

    /**
     * @return SecurityUser
     */
    public function getCurrentUser(): SecurityUser
    {
        return $this->currentUser;
    }

    /**
     * setCurrentUser
     * @param SecurityUser $currentUser
     * @return RollGroupListPagination
     */
    public function setCurrentUser(SecurityUser $currentUser): RollGroupListPagination
    {
        $this->currentUser = $currentUser;
        return $this;
    }
}
