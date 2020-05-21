<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Ebay\Synchronization\Orders\Reserve;

/**
 * Class Cancellation
 * @package Ess\M2ePro\Model\Ebay\Synchronization\Orders\Reserve
 */
class Cancellation extends \Ess\M2ePro\Model\Ebay\Synchronization\Orders\AbstractModel
{
    //########################################

    /**
     * @return string
     */
    protected function getNick()
    {
        return '/reserve_cancellation/';
    }

    /**
     * @return string
     */
    protected function getTitle()
    {
        return 'Reserve Cancellation';
    }

    // ---------------------------------------

    /**
     * @return int
     */
    protected function getPercentsStart()
    {
        return 0;
    }

    /**
     * @return int
     */
    protected function getPercentsEnd()
    {
        return 100;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    protected function intervalIsEnabled()
    {
        return true;
    }

    //########################################

    protected function performActions()
    {
        $permittedAccounts = $this->getPermittedAccounts();

        if (count($permittedAccounts) <= 0) {
            return;
        }

        $iteration = 1;
        $percentsForOneStep = $this->getPercentsInterval() / count($permittedAccounts);

        $this->getLog()->setInitiator(\Ess\M2ePro\Helper\Data::INITIATOR_EXTENSION);

        foreach ($permittedAccounts as $account) {
            /** @var $account \Ess\M2ePro\Model\Account **/

            // ---------------------------------------
            $this->getActualOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');

            // M2ePro\TRANSLATIONS
            // The "Reserve Cancellation" Action for eBay Account: "%account_title%" is started. Please wait...'
            $status = 'The "Reserve Cancellation" Action for eBay Account: "%account_title%" is started. ';
            $status .= 'Please wait...';
            $this->getActualLockItem()->setStatus(
                $this->getHelper('Module\Translation')->__($status, $account->getTitle())
            );
            // ---------------------------------------

            try {
                $this->processAccount($account);
            } catch (\Exception $exception) {
                $message = $this->getHelper('Module\Translation')->__(
                    'The "Reserve Cancellation" Action for eBay Account "%account%" was completed with error.',
                    $account->getTitle()
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }

            // ---------------------------------------
            // M2ePro\TRANSLATIONS
            // The "Reserve Cancellation" Action for eBay Account: "%account_title%" is finished. Please wait...
            $status = 'The "Reserve Cancellation" Action for eBay Account: "%account_title%" is finished. ';
            $status .= 'Please wait...';
            $this->getActualLockItem()->setStatus(
                $this->getHelper('Module\Translation')->__($status, $account->getTitle())
            );
            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $iteration * $percentsForOneStep);
            $this->getActualLockItem()->activate();
            // ---------------------------------------

            $iteration++;
        }
    }

    //########################################

    private function getPermittedAccounts()
    {
        $accountsCollection = $this->ebayFactory->getObject('Account')->getCollection();
        return $accountsCollection->getItems();
    }

    // ---------------------------------------

    private function processAccount(\Ess\M2ePro\Model\Account $account)
    {
        foreach ($this->getOrdersForRelease($account) as $order) {
            /** @var \Ess\M2ePro\Model\Order $order */
            $order->getReserve()->release();
        }
    }

    //########################################

    private function getOrdersForRelease(\Ess\M2ePro\Model\Account $account)
    {
        /** @var \Ess\M2ePro\Model\ResourceModel\Order\Collection $collection */
        $collection = $this->ebayFactory->getObject('Order')
            ->getCollection()
            ->addFieldToFilter('account_id', $account->getId())
            ->addFieldToFilter('reservation_state', \Ess\M2ePro\Model\Order\Reserve::STATE_PLACED);

        $reservationDays = (int)$account->getChildObject()->getQtyReservationDays();

        $minReservationStartDate = new \DateTime(
            $this->getHelper('Data')->getCurrentGmtDate(),
            new \DateTimeZone('UTC')
        );
        $minReservationStartDate->modify('- ' . $reservationDays . ' days');
        $minReservationStartDate = $minReservationStartDate->format('Y-m-d H:i');

        $collection->addFieldToFilter('reservation_start_date', ['lteq' => $minReservationStartDate]);

        return $collection->getItems();
    }

    //########################################
}
