<?php
/**
* 2010-2022 Webkul.
*
* NOTICE OF LICENSE
*
* All right is reserved,
* Please go through this link for complete license : https://store.webkul.com/license.html
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to https://store.webkul.com/customisation-guidelines/ for more information.
*
*  @author    Webkul IN <support@webkul.com>
*  @copyright 2010-2022 Webkul IN
*  @license   https://store.webkul.com/license.html
*/

class CartCustomerGuestDetailCore extends ObjectModel
{
    public $id_customer_guest_detail;
    public $id_cart;
    public $id_gender;
    public $firstname;
    public $lastname;
    public $email;
    public $phone;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'cart_customer_guest_detail',
        'primary' => 'id_customer_guest_detail',
        'fields' => array(
            'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_gender' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'firstname' => array('type' => self::TYPE_STRING, 'validate' => 'isName', 'size' => 32),
            'lastname' => array('type' => self::TYPE_STRING, 'validate' => 'isName', 'size' => 32),
            'email' => array('type' => self::TYPE_STRING, 'validate' => 'isEmail', 'size' => 128),
            'phone' => array('type' => self::TYPE_STRING, 'validate' => 'isPhoneNumber', 'size' => 32),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    public static function getCartCustomerGuest($id_cart)
    {
        return Db::getInstance()->getValue('
            SELECT `id_customer_guest_detail`
            FROM `'._DB_PREFIX_.'cart_customer_guest_detail`
            WHERE `id_cart` = '.(int)$id_cart
        );
    }

    public static function getCustomerGuestDetail($id_customer_guest_detail)
    {
        return Db::getInstance()->getRow('
            SELECT `id_gender`, `firstname`, `lastname`, `email`, `phone`
            FROM `'._DB_PREFIX_.'cart_customer_guest_detail`
            WHERE `id_customer_guest_detail` = '.(int)$id_customer_guest_detail
        );
    }

    public static function getCustomerDefaultDetails($email)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'cart_customer_guest_detail`
            WHERE `id_cart` = 0 AND `email` = "'.pSQL($email).'"'
        );
    }

    public static function updateCustomerPhoneNumber($email, $phone)
    {
        if ($customerDetail = self::getCustomerDefaultDetails($email)) {
            $objCartCustomerGuestDetail = new CartCustomerGuestDetail($customerDetail['id_customer_guest_detail']);
            $objCartCustomerGuestDetail->phone = $phone;
            return $objCartCustomerGuestDetail->save();
        } else {
            $objCustomer = new Customer();
            if (Validate::isLoadedObject($customer = $objCustomer->getByEmail($email, null, false))) {
                $objCartCustomerGuestDetail = new CartCustomerGuestDetail();
                $objCartCustomerGuestDetail->id_cart = 0;
                $objCartCustomerGuestDetail->id_gender = $customer->id_gender;
                $objCartCustomerGuestDetail->firstname = $customer->firstname;
                $objCartCustomerGuestDetail->lastname = $customer->lastname;
                $objCartCustomerGuestDetail->email = $customer->email;
                $objCartCustomerGuestDetail->phone = $phone;
                return $objCartCustomerGuestDetail->save();
            }
        }

        return false;
    }

    public static function getCustomerPhone($email)
    {
        return Db::getInstance()->getValue(
            'SELECT `phone` FROM `'._DB_PREFIX_.'cart_customer_guest_detail`
            WHERE `id_cart` = 0 AND `email` = "'.pSQL($email).'"'
        );
    }

    public function validateGuestInfo()
    {
        $isValid = true;
        if (!trim($this->firstname) || !Validate::isName($this->firstname)) {
            $isValid = false;
        }
        if (!trim($this->lastname) || !Validate::isName($this->lastname)) {
            $isValid = false;
        }
        if (!trim($this->email) || !Validate::isEmail($this->email)) {
            $isValid = false;
        }
        if (!trim($this->phone) || !Validate::isPhoneNumber($this->phone)) {
            $isValid = false;
        }

        $className = 'CartCustomerGuestDetail';
        $rules = call_user_func(array($className, 'getValidationRules'), $className);

        if (isset($rules['size']['firstname'])) {
            if (Tools::strlen(trim($this->firstname)) > $rules['size']['firstname']) {
                $isValid = false;
            }
        }
        if (isset($rules['size']['lastname'])) {
            if (Tools::strlen(trim($this->lastname)) > $rules['size']['lastname']) {
                $isValid = false;
            }
        }
        if (isset($rules['size']['email'])) {
            if (Tools::strlen(trim($this->email)) > $rules['size']['email']) {
                $isValid = false;
            }
        }
        if (isset($rules['size']['phone'])) {
            if (Tools::strlen(trim($this->phone)) > $rules['size']['phone']) {
                $isValid = false;
            }
        }

        return $isValid;
    }
}