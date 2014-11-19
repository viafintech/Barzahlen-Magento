<?php
/**
 * Barzahlen Payment Module for Magento
 *
 * @category    ZerebroInternet
 * @package     ZerebroInternet_Barzahlen
 * @copyright   Copyright (c) 2014 Cash Payment Solutions GmbH (https://www.barzahlen.de)
 * @author      Alexander Diebler
 * @author      Martin Seener
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL-3.0)
 */

class ZerebroInternet_Barzahlen_Block_Form extends Mage_Payment_Block_Form
{
    /**
     * Create the form block (Checkout Onepage) and assign the template to it.
     */
    protected function _construct()
    {
        $mark = Mage::getConfig()->getBlockClassName('core/template');
        $mark = new $mark;
        $mark->setTemplate('barzahlen/mark.phtml');
        $this->setTemplate('barzahlen/form.phtml')
             ->setMethodTitle('')
             ->setMethodLabelAfterHtml($mark->toHtml());

        return parent::_construct();
    }
}