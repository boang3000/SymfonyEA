<?php

namespace Arcanys\EasyAppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
	public function findByRole($role) {
		$qb = $this->_em->createQueryBuilder();
		$qb->select('u')
				->from($this->_entityName, 'u')
				->where('u.roles LIKE :roles')
				->setParameter('roles', '%"' . $role . '"%');
		return $qb->getQuery()->getResult();
	}

    public function getAdminEmails() {
        $admins = $this->findByRole('ROLE_ADMIN');

        $emails = [];
        foreach($admins as $key => $value) {
            $emails[] = $value->getEmail();
        }

        return $emails;
    }
}
