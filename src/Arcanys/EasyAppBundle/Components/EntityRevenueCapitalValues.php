<?php

namespace Arcanys\EasyAppBundle\Components;

use Doctrine\ORM\EntityManager;

class EntityRevenueCapitalValues
{
	
	protected $em;
	
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}
	
	public function getCurrentBalanceById($entity_id)
	{
		$rev = $this->_getCurrentRevenueValuesById($entity_id);
		$inv = $this->_getCurrentInvoiceValuesById($entity_id);
		$wire_incoming = $this->_getCurrentWireIncomingValuesById($entity_id);
		$wire_outgoing = $this->_getCurrentWireOutgoingValuesById($entity_id);
		$cap_to = $this->_getCurrentCapitalToValuesById($entity_id);
		$cap_from = $this->_getCurrentCapitalFromValuesById($entity_id);
		
		$n = 
			(isset($rev[0]) ? $rev[0]['curBalance'] : 0) + 
			(isset($rev[0]) ? $rev[0]['sum_revenue'] : 0) -
			(isset($inv[0]) ? $inv[0]['sum_invoice'] : 0) +
			(isset($wire_incoming[0]) ? $wire_incoming[0]['sum_wire'] : 0) -
			(isset($wire_outgoing[0]) ? $wire_outgoing[0]['sum_wire'] : 0) +
			(isset($cap_to[0]) ? $cap_to[0]['sum_cap'] : 0) -
			(isset($cap_from[0]) ? $cap_from[0]['sum_cap'] : 0);
		
		return round($n, 2);
	}
	
	public function getCurrentBalance($entity)
	{
		foreach($entity as $key => $val) {
			$rev = $this->_getCurrentRevenueValuesById($val['id']);
			$inv = $this->_getCurrentInvoiceValuesById($val['id']);
			$wire_incoming = $this->_getCurrentWireIncomingValuesById($val['id']);
			$wire_outgoing = $this->_getCurrentWireOutgoingValuesById($val['id']);
			$cap_to = $this->_getCurrentCapitalToValuesById($val['id']);
			$cap_from = $this->_getCurrentCapitalFromValuesById($val['id']);
			
			$n = 
				(isset($rev[0]) ? $rev[0]['curBalance'] : 0) + 
				(isset($rev[0]) ? $rev[0]['sum_revenue'] : 0) -
				(isset($inv[0]) ? $inv[0]['sum_invoice'] : 0) +
				(isset($wire_incoming[0]) ? $wire_incoming[0]['sum_wire'] : 0) -
				(isset($wire_outgoing[0]) ? $wire_outgoing[0]['sum_wire'] : 0) +
				(isset($cap_to[0]) ? $cap_to[0]['sum_cap'] : 0) -
				(isset($cap_from[0]) ? $cap_from[0]['sum_cap'] : 0);
				
			$entity[$key]['curBalance'] = round($n, 2);
		}
		
		return $entity;
	}
	
	public function getCurrentBalanceWireEntity($revenue)
	{
		$n = 0;
		foreach($revenue as $val) {
			$rev = $this->_getCurrentRevenueValuesById($revenue[$n][0]['entityId']);
			$inv = $this->_getCurrentInvoiceValuesById($revenue[$n][0]['entityId']);
			$wire_incoming = $this->_getCurrentWireIncomingValuesById($revenue[$n][0]['entityId']);
			$wire_outgoing = $this->_getCurrentWireOutgoingValuesById($revenue[$n][0]['entityId']);
			$cap_to = $this->_getCurrentCapitalToValuesById($revenue[$n][0]['entityId']);
			$cap_from = $this->_getCurrentCapitalFromValuesById($revenue[$n][0]['entityId']);
			
			$revenue[$n][0]['balance'] = 
							(isset($rev[0]) ? $rev[0]['curBalance'] : 0) + 
							(isset($rev[0]) ? $rev[0]['sum_revenue'] : 0) -
							(isset($inv[0]) ? $inv[0]['sum_invoice'] : 0) +
							(isset($wire_incoming[0]) ? $wire_incoming[0]['sum_wire'] : 0) -
							(isset($wire_outgoing[0]) ? $wire_outgoing[0]['sum_wire'] : 0) +
							(isset($cap_to[0]) ? $cap_to[0]['sum_cap'] : 0) -
							(isset($cap_from[0]) ? $cap_from[0]['sum_cap'] : 0);
							
			$revenue[$n][0]['balance'] = round($revenue[$n][0]['balance'], 2);
			$n++;
		}
		
		return $revenue;
	}

    public function getCurrentBalanceByBank($entity)
    {
        foreach($entity as $key => $val) {
            $rev = $this->_getCurrentRevenueValuesByBankId($val['entitybankId']);
            $inv = $this->_getCurrentInvoiceValuesByBankId($val['entitybankId']);
            $wire_incoming = $this->_getCurrentWireIncomingValuesByBankId($val['entitybankId']);
            $wire_outgoing = $this->_getCurrentWireOutgoingValuesByBankId($val['entitybankId']);
            $cap_to = $this->_getCurrentCapitalToValuesByBankId($val['entitybankId']);
            $cap_from = $this->_getCurrentCapitalFromValuesByBankId($val['entitybankId']);

            $n = $val['curBalance'] + $rev - $inv + $wire_incoming - $wire_outgoing +
                $cap_to - $cap_from;

            $entity[$key]['curBalance'] = round($n, 2);
        }

        return $entity;
    }
	
	private function _getCurrentRevenueValuesById($entity_id)
	{
		$query = $this->em->createQuery(
			"
			SELECT 
			  e.curBalance,
			  e.id,
			  e.company,
			  SUM(r.amount) AS sum_revenue
			FROM
			  ArcanysEasyAppBundle:Entity e 
			  LEFT JOIN ArcanysEasyAppBundle:Revenue r 
				WITH e.id = r.entityId
			WHERE e.id = :eid
			GROUP BY e.id
			ORDER BY e.dateadded DESC
			"
		);
		$query->setParameter('eid', $entity_id);
		return $query->getResult();
	}
	
	private function _getCurrentInvoiceValuesById($entity_id)
	{
		$query = $this->em->createQuery(
			"
			SELECT 
			  e.curBalance,
			  e.id,
			  SUM(i.amount) AS sum_invoice 
			FROM
			  ArcanysEasyAppBundle:Entity e 
			  LEFT JOIN ArcanysEasyAppBundle:Invoice i 
				WITH e.id = i.idEntity 
			WHERE ((i.bankinfo = 0 OR i.bankinfo IS NULL))
			AND	i.printready = 1
			AND	i.entityready = 1
			AND e.id = :eid
			GROUP BY e.id 
			"
		);
		$query->setParameter('eid', $entity_id);
		return $query->getResult();
	}
	
	private function _getCurrentWireIncomingValuesById($entity_id)
	{
		$query = $this->em->createQuery(
			"
			SELECT 
			  e.curBalance,
			  e.id,
			  SUM(r.amount) AS sum_wire
			FROM
			  ArcanysEasyAppBundle:Entity e 
			  LEFT JOIN ArcanysEasyAppBundle:Revenuewire r 
				WITH e.id = r.entityId 
			WHERE e.id = :eid
			AND r.wiretype = 0
			AND r.status = 2
			GROUP BY e.id
			ORDER BY e.dateadded DESC
			"
		);
		$query->setParameter('eid', $entity_id);
		return $query->getResult();
	}
	
	private function _getCurrentWireOutgoingValuesById($entity_id)
	{
		$query = $this->em->createQuery(
			"
			SELECT 
			  e.curBalance,
			  e.id,
			  SUM(r.amount) AS sum_wire
			FROM
			  ArcanysEasyAppBundle:Entity e 
			  LEFT JOIN ArcanysEasyAppBundle:Revenuewire r 
				WITH e.id = r.entityId 
			WHERE e.id = :eid
			AND r.wiretype = 1
			AND r.status = 2
			GROUP BY e.id
			ORDER BY e.dateadded DESC
			"
		);
		$query->setParameter('eid', $entity_id);
		return $query->getResult();
	}
	
	private function _getCurrentCapitalToValuesById($entity_id)
	{
		$query = $this->em->createQuery(
			"
			SELECT 
			  e.curBalance,
			  e.id,
			  SUM(r.amount) AS sum_cap
			FROM
			  ArcanysEasyAppBundle:Entity e 
			  LEFT JOIN ArcanysEasyAppBundle:Revenueinter r 
				WITH e.id = r.entityIdTo
			WHERE e.id = :eid
			GROUP BY e.id
			ORDER BY e.dateadded DESC
			"
		);
		$query->setParameter('eid', $entity_id);
		return $query->getResult();
	}
	
	private function _getCurrentCapitalFromValuesById($entity_id)
	{
		$query = $this->em->createQuery(
			"
			SELECT 
			  e.curBalance,
			  e.id,
			  SUM(r.amount) AS sum_cap
			FROM
			  ArcanysEasyAppBundle:Entity e 
			  LEFT JOIN ArcanysEasyAppBundle:Revenueinter r 
				WITH e.id = r.entityIdFrom
			WHERE e.id = :eid
			GROUP BY e.id
			ORDER BY e.dateadded DESC
			"
		);
		$query->setParameter('eid', $entity_id);
		return $query->getResult();
	}

    // Bank info
    private function _getCurrentRevenueValuesByBankId($entitybankId)
    {
        $query = $this->em->createQuery(
            "
            SELECT SUM(q.amount) AS total
            FROM ArcanysEasyAppBundle:Revenue q
            WHERE q.entitybanknameId = :entitybankId
            GROUP BY q.entitybanknameId
            "
        );
        $query->setParameter('entitybankId', $entitybankId);
        $result = $query->getResult();

        return count($result) > 0 ? $result[0]['total'] : 0;
    }

    private function _getCurrentInvoiceValuesByBankId($entitybankId)
    {
        $query = $this->em->createQuery(
            "
            SELECT SUM(q.amount) AS total
            FROM ArcanysEasyAppBundle:Invoice q
            WHERE q.bankinfo = :entitybankId
            AND q.printready = 1 AND q.entityready = 1
            GROUP BY q.bankinfo
            "
        );
        $query->setParameter('entitybankId', $entitybankId);
        $result = $query->getResult();

        return count($result) > 0 ? $result[0]['total'] : 0;
    }

    private function _getCurrentWireIncomingValuesByBankId($entitybankId)
    {
        $query = $this->em->createQuery(
            "
            SELECT SUM(q.amount) AS total
            FROM ArcanysEasyAppBundle:Revenuewire q
            WHERE q.entitybanknameId = :entitybankId
            AND q.wiretype = 0 AND q.status = 2
            GROUP BY q.entitybanknameId
            "
        );

        $query->setParameter('entitybankId', $entitybankId);
        $result = $query->getResult();

        return count($result) > 0 ? $result[0]['total'] : 0;
    }

    private function _getCurrentWireOutgoingValuesByBankId($entitybankId)
    {
        $query = $this->em->createQuery(
            "
            SELECT SUM(q.amount) AS total
            FROM ArcanysEasyAppBundle:Revenuewire q
            WHERE q.entitybanknameId = :entitybankId
            AND q.wiretype = 1 AND q.status = 2
            GROUP BY q.entitybanknameId
            "
        );

        $query->setParameter('entitybankId', $entitybankId);
        $result = $query->getResult();

        return count($result) > 0 ? $result[0]['total'] : 0;
    }

    private function _getCurrentCapitalToValuesByBankId($entitybankId)
    {
        $query = $this->em->createQuery(
            "
            SELECT SUM(q.amount) AS total
            FROM ArcanysEasyAppBundle:Revenueinter q
            WHERE q.entitybanknameIdTo = :entitybankId
            GROUP BY q.entitybanknameIdTo
            "
        );

        $query->setParameter('entitybankId', $entitybankId);
        $result = $query->getResult();

        return count($result) > 0 ? $result[0]['total'] : 0;
    }

    private function _getCurrentCapitalFromValuesByBankId($entitybankId)
    {
        $query = $this->em->createQuery(
            "
            SELECT SUM(q.amount) AS total
            FROM ArcanysEasyAppBundle:Revenueinter q
            WHERE q.entityIdFrom = :entitybankId
            GROUP BY q.entityIdFrom
            "
        );

        $query->setParameter('entitybankId', $entitybankId);
        $result = $query->getResult();

        return count($result) > 0 ? $result[0]['total'] : 0;
    }

}