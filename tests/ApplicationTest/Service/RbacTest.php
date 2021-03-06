<?php

namespace ApplicationTest\Service;

class RbacTest extends \ApplicationTest\Controller\AbstractController
{

    public function testRbac()
    {
        $geoname = new \Application\Model\Geoname();

        $user = new \Application\Model\User();
        $user->setPassword('foo')->setName('test user');

        $survey = new \Application\Model\Survey();
        $survey->setName('test survey')->setIsActive(true)->setCode('test code');

        $survey2 = new \Application\Model\Survey();
        $survey2->setName('test survey2')->setIsActive(true)->setCode('test code2');

        $questionnaire = new \Application\Model\Questionnaire();
        $questionnaire->setDateObservationStart(new \DateTime())->setDateObservationEnd(new \DateTime())->setSurvey($survey)->setGeoname($geoname);

        $questionnaire2 = new \Application\Model\Questionnaire();
        $questionnaire2->setDateObservationStart(new \DateTime())->setDateObservationEnd(new \DateTime())->setSurvey($survey2)->setGeoname($geoname);

        $filterSet = new \Application\Model\FilterSet('filterSet 1');
        $filterSet2 = new \Application\Model\FilterSet('filterSet 2');
        $filterSet3 = new \Application\Model\FilterSet('filterSet 3');

        $filter1 = new \Application\Model\Filter('filter 1');
        $filter2 = new \Application\Model\Filter('filter 2');
        $filter3 = new \Application\Model\Filter('filter 3');
        $filter11 = new \Application\Model\Filter('filter 11'); // child of $f1
        $filter12 = new \Application\Model\Filter('filter 12'); // child of $f1
        $filter21 = new \Application\Model\Filter('filter 21'); // child of $f2
        $filter22 = new \Application\Model\Filter('filter 22'); // child of $f2
        $filter1->addChild($filter11)->addChild($filter12);
        $filter2->addChild($filter21)->addChild($filter22);

        $filterSet->addFilter($filter1)->addFilter($filter2);
        $filterSet2->addFilter($filter1)->addFilter($filter3);
        $filterSet3->addFilter($filter1)->addFilter($filter2);

        $role = new \Application\Model\Role('role global');
        $roleSurvey = new \Application\Model\Role('role survey');
        $roleQuestionnaire = new \Application\Model\Role('role questionnaire');
        $roleFilterSet = new \Application\Model\Role('role filterset');

        $permission = new \Application\Model\Permission('permission global');
        $permissionSurvey = new \Application\Model\Permission('permission survey');
        $permissionQuestionnaire = new \Application\Model\Permission('permission questionnaire');
        $permissionFilterSet = new \Application\Model\Permission('permission filterset');

        $role->addPermission($permission);
        $role->addPermission($permissionSurvey);
        $role->addPermission($permissionQuestionnaire);
        $role->addPermission($permissionFilterSet);

        $roleSurvey->addPermission($permission);
        $roleSurvey->addPermission($permissionSurvey);

        $roleQuestionnaire->addPermission($permission);
        $roleQuestionnaire->addPermission($permissionQuestionnaire);

        $roleFilterSet->addPermission($permission);
        $roleFilterSet->addPermission($permissionFilterSet);

        $userSurvey = new \Application\Model\UserSurvey();
        $userSurvey->setUser($user)->setSurvey($survey)->setRole($roleSurvey);

        $userQuestionnaire = new \Application\Model\UserQuestionnaire();
        $userQuestionnaire->setUser($user)->setQuestionnaire($questionnaire)->setRole($roleQuestionnaire);

        $userFilterSet = new \Application\Model\UserFilterSet();
        $userFilterSet->setUser($user)->setFilterSet($filterSet)->setRole($roleFilterSet);

        $userFilterSet3 = new \Application\Model\UserFilterSet();
        $userFilterSet3->setUser($user)->setFilterSet($filterSet3)->setRole($roleFilterSet);

        $this->getEntityManager()->persist($geoname);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->persist($survey);
        $this->getEntityManager()->persist($survey2);
        $this->getEntityManager()->persist($questionnaire);
        $this->getEntityManager()->persist($questionnaire2);
        $this->getEntityManager()->persist($filterSet);
        $this->getEntityManager()->persist($filterSet2);
        $this->getEntityManager()->persist($filterSet3);
        $this->getEntityManager()->persist($filter1);
        $this->getEntityManager()->persist($filter2);
        $this->getEntityManager()->persist($filter3);
        $this->getEntityManager()->persist($filter11);
        $this->getEntityManager()->persist($filter12);
        $this->getEntityManager()->persist($filter21);
        $this->getEntityManager()->persist($filter22);
        $this->getEntityManager()->persist($role);
        $this->getEntityManager()->persist($roleSurvey);
        $this->getEntityManager()->persist($roleQuestionnaire);
        $this->getEntityManager()->persist($roleFilterSet);
        $this->getEntityManager()->persist($permission);
        $this->getEntityManager()->persist($permissionSurvey);
        $this->getEntityManager()->persist($permissionQuestionnaire);
        $this->getEntityManager()->persist($permissionFilterSet);
        $this->getEntityManager()->persist($userSurvey);
        $this->getEntityManager()->persist($userQuestionnaire);
        $this->getEntityManager()->persist($userFilterSet);
        $this->getEntityManager()->persist($userFilterSet3);
        $this->getEntityManager()->flush();

        /* @var $rbac \Application\Service\Rbac */
        $rbac = $this->getApplicationServiceLocator()->get('ZfcRbac\Service\Rbac');

        $rbac->setIdentity(null);
        $this->assertTrue($rbac->hasRole('anonymous'), 'Not logged in users have builtin anonymous role');
        $this->assertFalse($rbac->hasRole('member'), 'Not logged in users does not have builtin member role');

        $rbac->setIdentity($user);
        $this->assertFalse($rbac->hasRole('anonymous'), 'Logged in users does not have builtin anonymous role');
        $this->assertTrue($rbac->hasRole('member'), 'Logged in users have builtin member role');
        $this->assertFalse($rbac->isGranted('non existing permission name'), 'non existing permission is denied');

        // Test without context
        $this->assertTrue($rbac->isGranted($permission->getName()), 'without context, global permission is granted');
        $this->assertTrue($rbac->isGranted($permissionSurvey->getName()), 'without context, survey permission is granted');
        $this->assertTrue($rbac->isGranted($permissionQuestionnaire->getName()), 'without context, questionnaire permission is granted');
        $this->assertTrue($rbac->isGranted($permissionFilterSet->getName()), 'without context, filterSet permission is granted');

        // Test with Survey context
        $this->assertTrue($rbac->isGrantedWithContext($survey, $permission->getName()), 'with survey context, global permission is still granted');
        $this->assertTrue($rbac->isGrantedWithContext($survey, $permissionSurvey->getName()), 'with survey context, survey permission is granted');
        $this->assertFalse($rbac->isGrantedWithContext($survey, $permissionQuestionnaire->getName()), 'with survey context, questionnaire permission is denied');
        $this->assertFalse($rbac->isGrantedWithContext($survey, $permissionFilterSet->getName()), 'with survey context, filterSet permission is denied');

        // Test with Questionnaire context
        $this->assertTrue($rbac->isGrantedWithContext($questionnaire, $permission->getName()), 'with questionnaire context, global permission is still granted');
        $this->assertFalse($rbac->isGrantedWithContext($questionnaire, $permissionSurvey->getName()), 'with questionnaire context, survey permission is denied');
        $this->assertTrue($rbac->isGrantedWithContext($questionnaire, $permissionQuestionnaire->getName()), 'with questionnaire context, questionnaire permission is granted');
        $this->assertFalse($rbac->isGrantedWithContext($questionnaire, $permissionFilterSet->getName()), 'with questionnaire context, filterSet permission is granted');

        // Test with Filterset context
        $this->assertTrue($rbac->isGrantedWithContext($filterSet, $permission->getName()), 'with filterSet context, global permission is still granted');
        $this->assertFalse($rbac->isGrantedWithContext($filterSet, $permissionSurvey->getName()), 'with filterSet context, survey permission is denied');
        $this->assertFalse($rbac->isGrantedWithContext($filterSet, $permissionQuestionnaire->getName()), 'with filterSet context, questionnaire permission is denied');
        $this->assertTrue($rbac->isGrantedWithContext($filterSet, $permissionFilterSet->getName()), 'with filterSet context, filterSet permission is granted');

        // Test with wrong Survey context
        $this->assertFalse($rbac->isGrantedWithContext($survey2, $permission->getName()), 'with wrong survey context, global permission is denied');
        $this->assertFalse($rbac->isGrantedWithContext($survey2, $permissionSurvey->getName()), 'with wrong survey context, survey permission is denied');
        $this->assertFalse($rbac->isGrantedWithContext($survey2, $permissionQuestionnaire->getName()), 'with wrong survey context, questionnaire permission is denied');
        $this->assertFalse($rbac->isGrantedWithContext($survey2, $permissionFilterSet->getName()), 'with wrong survey context, filterSet permission is denied');

        // Test with wrong Questionnaire context
        $this->assertFalse($rbac->isGrantedWithContext($questionnaire2, $permission->getName()), 'with wrong questionnaire context, global permission is denied');
        $this->assertFalse($rbac->isGrantedWithContext($questionnaire2, $permissionSurvey->getName()), 'with wrong questionnaire context, survey permission is denied');
        $this->assertFalse($rbac->isGrantedWithContext($questionnaire2, $permissionQuestionnaire->getName()), 'with wrong questionnaire context, questionnaire permission is denied');
        $this->assertFalse($rbac->isGrantedWithContext($questionnaire2, $permissionFilterSet->getName()), 'with wrong questionnaire context, filterSet permission is denied');

        // Test with wrong FilterSet context
        $this->assertFalse($rbac->isGrantedWithContext($filterSet2, $permission->getName()), 'with wrong filterSet context, global permission is denied');
        $this->assertFalse($rbac->isGrantedWithContext($filterSet2, $permissionSurvey->getName()), 'with wrong filterSet context, survey permission is denied');
        $this->assertFalse($rbac->isGrantedWithContext($filterSet2, $permissionQuestionnaire->getName()), 'with wrong filterSet context, questionnaire permission is denied');
        $this->assertFalse($rbac->isGrantedWithContext($filterSet2, $permissionFilterSet->getName()), 'with wrong filterSet context, filterSet permission is denied');

        // test permissions on filters
        $this->assertCount(3, $filter1->getRoleContext(''), 'filter 1 should have 3 filterSets');
        $this->assertFalse($rbac->isGrantedWithContext($filter1->getRoleContext(''), $permissionFilterSet->getName()), 'with filterset 1 & 2 & 3, permission is denied, restricted by filterset 2');
        $this->assertCount(2, $filter2->getRoleContext(''), 'filter 2 should have 1 filterSets');
        $this->assertTrue($rbac->isGrantedWithContext($filter2->getRoleContext('read'), $permissionFilterSet->getName()), 'with filterset 1 & 3 context, permission is granted');
        $this->assertCount(1, $filter3->getRoleContext(''), 'filter 3 should have 2 filterSets');
        $this->assertFalse($rbac->isGrantedWithContext($filter3->getRoleContext(''), $permissionFilterSet->getName()), 'with filterset 2 , permission is denied');

        // test permissions on child filters
        $this->assertCount(3, $filter11->getRoleContext(''), 'filter 11 should have 2 filterSets');
        $this->assertFalse($rbac->isGrantedWithContext($filter11->getRoleContext(''), $permissionFilterSet->getName()), 'with filterset 11 , permission is denied');
        $this->assertCount(3, $filter12->getRoleContext(''), 'filter 12 should have 2 filterSets');
        $this->assertFalse($rbac->isGrantedWithContext($filter12->getRoleContext(''), $permissionFilterSet->getName()), 'with filterset 12 , permission is denied');
        $this->assertCount(2, $filter21->getRoleContext(''), 'filter 21 should have 2 filterSets');
        $this->assertTrue($rbac->isGrantedWithContext($filter21->getRoleContext(''), $permissionFilterSet->getName()), 'with filterset 21 , permission is granted');
        $this->assertCount(2, $filter22->getRoleContext(''), 'filter 22 should have 2 filterSets');
        $this->assertTrue($rbac->isGrantedWithContext($filter22->getRoleContext(''), $permissionFilterSet->getName()), 'with filterset 21 , permission is granted');

        // Test with non persistent context
        $nonPersistedSurvey = new \Application\Model\Survey();
        $userSurvey->setUser($user)->setSurvey($nonPersistedSurvey)->setRole($roleSurvey);
        $this->assertTrue($rbac->isGrantedWithContext($nonPersistedSurvey, $permission->getName()), 'permission with non persistent context is granted');

        // Test error messages
        $this->assertTrue($rbac->isActionGranted($filterSet, 'read'));
        $this->assertNull($rbac->getMessage(), 'no message with granted action');
        $this->assertFalse($rbac->isActionGranted($filterSet, 'update'));
        $expectedMessage = 'Insufficient access rights for permission "FilterSet-update" on "Application\Model\FilterSet#' . $filterSet->getId() . ' (filterSet 1)"  with your current roles [member, role filterset] in context "Application\Model\FilterSet#' . $filterSet->getId() . '" (filterSet 1)';
        $this->assertSame($expectedMessage, $rbac->getMessage(), 'error message for single context object');
        $this->assertTrue($rbac->isActionGranted($filter1, 'read'));
        $this->assertNull($rbac->getMessage(), 'no message with granted action');
        $this->assertFalse($rbac->isActionGranted($filter1, 'update'));
        $expectedMessage = 'Insufficient access rights for permission "Filter-update" on "Application\Model\Filter#' . $filter1->getId() . ' (filter 1)"  with your current roles [member, role filterset] in context "Application\Model\FilterSet#' . $filterSet->getId() . '" (filterSet 1) and "Application\Model\FilterSet#' . $filterSet2->getId() . '" (filterSet 2) and "Application\Model\FilterSet#' . $filterSet3->getId() . '" (filterSet 3)';
        $this->assertSame($expectedMessage, $rbac->getMessage(), 'error message for multiple context object');

    }
}
