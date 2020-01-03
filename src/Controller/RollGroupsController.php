<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 27/07/2019
 * Time: 10:52
 */

namespace Kookaburra\RollGroups\Controller;

use App\Container\ContainerManager;
use App\Twig\Extension\SettingExtension;
use App\Twig\Sidebar\Photo;
use Kookaburra\RollGroups\Entity\RollGroup;
use Kookaburra\RollGroups\Form\DetailStudentSortType;
use Kookaburra\RollGroups\Form\RollGroupType;
use Kookaburra\RollGroups\Pagination\RollGroupListPagination;
use Kookaburra\RollGroups\Pagination\RollGroupPagination;
use Kookaburra\SchoolAdmin\Entity\AcademicYear;
use Kookaburra\UserAdmin\Entity\Person;
use App\Provider\ProviderFactory;
use App\Twig\SidebarContent;
use App\Twig\TableViewManager;
use Kookaburra\UserAdmin\Util\SecurityHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class RollGroupsController
 * @package App\Controller\Modules
 */
class RollGroupsController extends AbstractController
{
    /**
     * list
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param RollGroupListPagination $pagination
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/list/", name="list")
     * @Route("/")
     * @Security("is_granted('ROLE_ROUTE', ['roll_groups__list'])")
     */
    public function list(Request $request, TranslatorInterface $translator, RollGroupListPagination $pagination)
    {
        $rollGroups = ProviderFactory::getRepository(RollGroup::class)->findBy(['academicYear' => ProviderFactory::getRepository(AcademicYear::class)->find($request->getSession()->get('academicYear', null))],['name' => 'ASC']);

        $pagination->setCurrentUser($this->getUser())->setContent($rollGroups)->setPaginationScript();

        return $this->render('@KookaburraRollGroups/list.html.twig');
    }

    /**
     * detail
     * @param RollGroup $rollGroup
     * @param Request $request
     * @param SidebarContent $sidebar
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     * @Route("/{rollGroup}/detail/", name="detail")
     * @IsGranted("ROLE_ROUTE")
     */
    public function detail(RollGroup $rollGroup, Request $request, SidebarContent $sidebar, ContainerManager $manager)
    {
        if ($rollGroup->getTutor()) {
            $image = new Photo($rollGroup->getTutor(), 'getImage240', 200, 'max200 user');
            $sidebar->addContent($image);
        }

        $canPrint = SecurityHelper::isActionAccessible('/modules/Students/report_students_byRollGroup_print.php');

        $highestAction = SecurityHelper::getHighestGroupedAction('/modules/Students/student_view_details.php');

        $canViewStudents = ($highestAction == 'View Student Profile_brief' || $highestAction == 'View Student Profile_full' || $highestAction == 'View Student Profile_fullNoNotes');

        $form = $this->createForm(DetailStudentSortType::class);
        $form->handleRequest($request);

        $sortBy = $request->request->has('detail_student_sort') ? $request->request->get('detail_student_sort')['sortBy'] : 'rollOrder';

        return $this->render('@KookaburraRollGroups/details.html.twig',
            [
                'rollGroup' => $rollGroup,
                'staffView' => SecurityHelper::isActionAccessible('/modules/Staff/staff_view_details.php'),
                'sortBy' => $sortBy,
                'form' => $form->createView(),
                'canPrint' => $canPrint,
                'canViewStudents' => $canViewStudents,
                'students' => ProviderFactory::getRepository(Person::class)->findStudentsByRollGroup($rollGroup, $sortBy),
            ]
        );
    }
    /**
     * manage
     * @Route("/manage/",name="manage")
     * @IsGranted("ROLE_ROUTE")
     * @return
     */
    public function manage(RollGroupPagination $pagination)
    {
        $content = ProviderFactory::getRepository(RollGroup::class)->findBy([],['name' => 'ASC']);
        $pagination->setContent($content)->setPageMax(25)
            ->setPaginationScript();
        return $this->render('@KookaburraRollGroups/manage.html.twig');
    }

    /**
     * edit
     * @Route("/{roll}/edit/", name="edit")
     * @Route("/year/group/add/", name="add")
     * @IsGranted("ROLE_ROUTE")
     */
    public function edit(ContainerManager $manager, Request $request, ?RollGroup $roll = null)
    {
        if (!$roll instanceof RollGroup) {
            $roll = new RollGroup();
            $action = $this->generateUrl('roll_groups__add');
        } else {
            $action = $this->generateUrl('roll_groups__edit', ['roll' => $roll->getId()]);
        }

        $form = $this->createForm(RollGroupType::class, $roll, ['action' => $action]);

        if ($request->getContentType() === 'json') {
            $content = json_decode($request->getContent(), true);
            $form->submit($content);
            $data = [];
            $data['status'] = 'success';
            if ($form->isValid()) {
                $provider = ProviderFactory::create(YearGroup::class);
                $data = $provider->persistFlush($roll, $data);
                if ($data['status'] === 'success') {
                    $form = $this->createForm(YearGroupType::class, $roll, ['action' => $this->generateUrl('school_admin__year_group_edit', ['year' => $roll->getId()])]);
                }
            } else {
                $data['errors'][] = ['class' => 'error', 'message' => TranslationsHelper::translate('return.error.1', [], 'messages')];
                $data['status'] = 'error';
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer('formContent', 'single');

            return new JsonResponse($data, 200);
        }
        $manager->singlePanel($form->createView());

        return $this->render('@KookaburraRollGroups/edit.html.twig',
            [
                'roll' => $roll,
            ]
        );
    }

    /**
     * delete
     * @Route("/{roll}/delete/", name="delete")
     * @IsGranted("ROLE_ROUTE")
     */
    public function delete(RollGroup $roll, FlashBagInterface $flashBag, TranslatorInterface $translator)
    {
        $provider = ProviderFactory::create(RollGroup::class);

        $provider->delete($roll);

        $provider->getMessageManager()->pushToFlash($flashBag, $translator);

        return $this->redirectToRoute('roll_groups__manage');
    }
}
