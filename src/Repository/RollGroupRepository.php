<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace Kookaburra\RollGroups\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Kookaburra\RollGroups\Entity\RollGroup;
use Kookaburra\SchoolAdmin\Entity\Facility;
use Kookaburra\SchoolAdmin\Util\AcademicYearHelper;
use Kookaburra\UserAdmin\Entity\Person;
use Kookaburra\SchoolAdmin\Entity\AcademicYear;
use Kookaburra\UserAdmin\Util\UserHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class RollGroupRepository
 * @package App\Repository
 */
class RollGroupRepository extends ServiceEntityRepository
{
    /**
     * ApplicationFormRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RollGroup::class);
    }

    /**
     * findByTutor
     * @param Person $tutor
     * @return array
     */
    public function findByTutor(Person $tutor, ?AcademicYear $schoolYear): array
    {
        $schoolYear = $schoolYear ?: AcademicYearHelper::getCurrentAcademicYear();
        return $this->createQueryBuilder('rg')
            ->select('rg')
            ->where('rg.tutor = :person OR rg.tutor2 = :person OR rg.tutor3 = :person OR rg.assistant = :person OR rg.assistant2 = :person OR rg.assistant3 = :person')
            ->setParameter('person', $tutor)
            ->andWhere('rg.academicYear = :academicYear')
            ->setParameter('academicYear', $schoolYear)
            ->getQuery()
            ->getResult();
    }

    /**
     * findOneByPersonSchoolYear
     * @param Person $person
     * @param AcademicYear $schoolYear
     * @return RollGroup|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByPersonSchoolYear(Person $person, AcademicYear $schoolYear): ?RollGroup
    {
        if (UserHelper::isStaff())
            return $this->findOneBy(['tutor' => $person, 'academicYear' => $schoolYear]);
        return $this->findOneByStudent($person, $schoolYear);
    }

    /**
     * findOneByStudent
     * @param Person $person
     * @param AcademicYear|null $schoolYear
     * @return RollGroup|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByStudent(Person $person, ?AcademicYear $schoolYear): ?RollGroup
    {
        $schoolYear = $schoolYear ?: AcademicYearHelper::getCurrentAcademicYear();
        return $this->createQueryBuilder('rg')
            ->select('rg')
            ->leftJoin('rg.studentEnrolments', 'se')
            ->where('se.person = :person')
            ->setParameter('person', $person)
            ->andWhere('rg.academicYear = :academicYear')
            ->andWhere('se.academicYear = :academicYear')
            ->setParameter('academicYear', $schoolYear)
            ->orderBy('se.rollOrder', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * findByAcademicYear
     * @param AcademicYear $year
     */
    public function findByAcademicYear(AcademicYear $year): array
    {
        return $this->createQueryBuilder('r')
            ->select(['r','s','t','staff'])
            ->leftJoin('r.space', 's')
            ->leftJoin('r.tutor', 't')
            ->leftJoin('t.staff', 'staff')
            ->leftJoin('r.studentEnrolments', 'se')
            ->where('r.academicYear = :year')
            ->setParameter('year', $year)
            ->orderBy('r.name')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * countFacility
     * @param Facility $facility
     * @return int
     */
    public function countFacility(Facility $facility): int
    {
        try {
            return intval($this->createQueryBuilder('r')
                ->select('COUNT(r.id)')
                ->where('r.facility = :facility')
                ->setParameter('facility', $facility)
                ->getQuery()
                ->getSingleScalarResult());
        } catch ( NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }
}
