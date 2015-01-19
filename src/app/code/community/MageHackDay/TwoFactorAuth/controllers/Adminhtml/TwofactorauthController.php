<?php

/**
 * adminhtml controller to enforce Two Factor Authentication
 *
 * @category    MageHackDay
 * @package     MageHackDay_TwoFactorAuth
 * @author      Jonathan Day <jonathan@aligent.com.au>
 */
class MageHackDay_TwoFactorAuth_Adminhtml_TwofactorauthController extends Mage_Adminhtml_Controller_Action{


    public function interstitialAction(){
        $user = Mage::getSingleton('admin/session')->getUser(); /** @var $user Mage_Admin_Model_User */
        if (Mage::helper('twofactorauth/auth')->isAuthorized($user)) {
            Mage::getSingleton('adminhtml/session')->unsTfaNotEntered(true);
            $this->_redirect('*');
            return;
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    public function verifyAction(){
        $oRequest = Mage::app()->getRequest();
        $vInputCode = $oRequest->getPost('input_code',null);
        $rememberMe = (bool) $oRequest->getPost('remember_me', false);
        $authHelper = Mage::helper('twofactorauth/auth');
        $oUser = Mage::getSingleton('admin/session')->getUser(); /** @var $oUser Mage_Admin_Model_User */
        $vSecret = $oUser->getTwofactorToken();
        if(!$vSecret){
            //user is accessing protected route without configured TFA
            return $this;
        }
        $bValid = $authHelper->verifyCode($vInputCode, $vSecret);
        if($bValid === false){
            Mage::getSingleton('adminhtml/session')->addError('Two Factor Authentication has failed. Please try again or contact an administrator');
            $this->_redirect('adminhtml/twofactorauth/interstitial');
            return $this;
        }
        if ($rememberMe) {
            try {
                $cookie = $authHelper->generateCookie();
                Mage::getResourceModel('twofactorauth/user_cookie')->saveCookie($oUser->getId(), $cookie);
                $authHelper->setCookie($cookie);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        Mage::getSingleton('adminhtml/session')->unsTfaNotEntered(true);
        $this->_redirect('*');
        return $this;
    }

    /**
     * Clear cookies for the current user
     */
    public function clearCookiesAction()
    {
        try {
            Mage::getResourceModel('twofactorauth/user_cookie')->deleteCookies(Mage::getSingleton('admin/session')->getUser());
            $this->_getSession()->addSuccess(Mage::helper('twofactorauth')->__('2FA cookies have been successfully deleted.'));
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('twofactorauth')->__('An error occurred while deleting 2FA cookies.'));
            Mage::logException($e);
        }

        $this->_redirect('*/system_account/index');
    }

    /**
     * Display one time secret question
     */
    public function questionAction()
    {
        $collection = Mage::getResourceModel('twofactorauth/user_question_collection')
            ->addUserFilter(Mage::getSingleton('admin/session')->getUser())
            ->setRandomOrder();
        $collection->setCurPage(1)->setPageSize(1);
        $question = $collection->getFirstItem();
        if ( ! $question->getId()) {
            $this->_getSession()->addError(Mage::helper('twofactorauth')->__('Cannot load security question.'));
            $this->_redirect('*/*/interstitial');
            return;
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Check answer to the question
     */
    public function answerAction()
    {
        $questionId = (int) Mage::app()->getRequest()->getPost('question_id');
        if ( ! $questionId) {
            $this->_redirect('*/*/interstitial');
            $this->_getSession()->addError(Mage::helper('twofactorauth')->__('Unknown question.'));
            return;
        }
        $answer = (string) Mage::app()->getRequest()->getPost('answer');
        if (empty($answer)) {
            $this->_redirect('*/*/interstitial');
            $this->_getSession()->addError(Mage::helper('twofactorauth')->__('Please enter your answer to the secret question.'));
            return;
        }
        $user = Mage::getSingleton('admin/session')->getUser(); /** @var $user Mage_Admin_Model_User */
        $question = Mage::getModel('twofactorauth/user_question')->load($questionId);
        if ( ! $question->getId() || $question->getUserId() != $user->getId()) {
            $this->_redirect('*/*/interstitial');
            $this->_getSession()->addError(Mage::helper('twofactorauth')->__('Cannot load the secret question.'));
            return;
        }
        if ($question->getAnswer() != $answer) {
            $this->_redirect('*/*/interstitial');
            $this->_getSession()->addError(Mage::helper('twofactorauth')->__('Answer to the secret question is invalid.'));
            return;
        }

        Mage::getSingleton('adminhtml/session')->unsTfaNotEntered(true);
        $question->delete();
        $this->_redirect('*');
        return;
    }

}