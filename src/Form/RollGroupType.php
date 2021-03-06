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
 * Time: 16:05
 */

namespace Kookaburra\RollGroups\Form;

use Kookaburra\SchoolAdmin\Entity\Facility;
use App\Form\Type\DisplayType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Provider\ProviderFactory;
use Doctrine\ORM\EntityRepository;
use Kookaburra\RollGroups\Entity\RollGroup;
use Kookaburra\SchoolAdmin\Util\AcademicYearHelper;
use Kookaburra\UserAdmin\Entity\Person;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RollGroupType
 * @package Kookaburra\RollGroups\Form
 */
class RollGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('academicYear', DisplayType::class,
                [
                    'label' => 'Academic Year',
                    'help' => 'This value cannot be changed.',
                    'mapped' => false,
                    'data' => $options['data']->getAcademicYear()->getName(),
                ]
            )
            ->add('name', TextType::class,
                [
                    'label' => 'Name',
                    'help' => 'Needs to be unique in the academic year.',
                ]
            )
            ->add('nameShort', TextType::class,
                [
                    'label' => 'Abbreviation',
                    'help' => 'Needs to be unique in the academic year.',
                ]
            )
            ->add('tutor', EntityType::class,
                [
                    'label' => 'Main Tutor',
                    'class' => Person::class,
                    'placeholder' => ' ',
                    'choice_label' => 'fullNameReversed',
                    'choice_loader' => new CallbackChoiceLoader(function() {
                        return ProviderFactory::create(Person::class)->getCurrentStaffChoiceList(true);
                    }),
                ]
            )
            ->add('tutor2', EntityType::class,
                [
                    'label' => '2nd Tutor',
                    'class' => Person::class,
                    'placeholder' => ' ',
                    'choice_label' => 'fullNameReversed',
                    'choice_loader' => new CallbackChoiceLoader(function() {
                        return ProviderFactory::create(Person::class)->getCurrentStaffChoiceList(true);
                    }),
                ]
            )
            ->add('tutor3', EntityType::class,
                [
                    'label' => '3rd Tutor',
                    'class' => Person::class,
                    'placeholder' => ' ',
                    'choice_label' => 'fullNameReversed',
                    'choice_loader' => new CallbackChoiceLoader(function() {
                        return ProviderFactory::create(Person::class)->getCurrentStaffChoiceList(true);
                    }),
                ]
            )
            ->add('assistant', EntityType::class,
                [
                    'label' => 'Educational Assistant',
                    'class' => Person::class,
                    'choice_label' => 'fullNameReversed',
                    'placeholder' => ' ',
                    'choice_loader' => new CallbackChoiceLoader(function() {
                        return ProviderFactory::create(Person::class)->getCurrentStaffChoiceList(true);
                    }),
                ]
            )
            ->add('assistant2', EntityType::class,
                [
                    'label' => '2nd Educational Assistant',
                    'class' => Person::class,
                    'placeholder' => ' ',
                    'choice_label' => 'fullNameReversed',
                    'choice_loader' => new CallbackChoiceLoader(function() {
                        return ProviderFactory::create(Person::class)->getCurrentStaffChoiceList(true);
                    }),
                ]
            )
            ->add('assistant3', EntityType::class,
                [
                    'label' => '3rd Educational Assistant',
                    'class' => Person::class,
                    'choice_label' => 'fullNameReversed',
                    'placeholder' => ' ',
                    'choice_loader' => new CallbackChoiceLoader(function() {
                        return ProviderFactory::create(Person::class)->getCurrentStaffChoiceList(true);
                    }),
                ]
            )
            ->add('space', EntityType::class,
                [
                    'label' => 'Room',
                    'class' => Space::class,
                    'placeholder' => ' ',
                    'choice_label' => 'name',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('s')
                            ->orderBy('s.name')
                            ;
                    },
                ]
            )
            ->add('nextRollGroup', EntityType::class,
                [
                    'label' => 'Next Roll Group',
                    'help' => 'Sets student progression on rollover.',
                    'class' => RollGroup::class,
                    'placeholder' => ' ',
                    'choice_label' => 'name',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                            ->orderBy('r.name')
                            ->where('r.academicYear = :year')
                            ->setParameter('year', AcademicYearHelper::getCurrentAcademicYear())
                        ;
                    },
                ]
            )
            ->add('attendance', ToggleType::class,
                [
                    'label' => 'Track Attendance',
                    'help' => 'Should this class allow attendance to be taken?',
                ]
            )
            ->add('website', UrlType::class,
                [
                    'label' => 'Website',
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'label' => 'Submit',
                ]
            )
        ;
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'RollGroups',
                'data_class' => RollGroup::class,
            ]
        );
    }

    /**
     * getParent
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}