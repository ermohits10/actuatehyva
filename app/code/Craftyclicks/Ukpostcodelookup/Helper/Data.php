<?php
namespace Craftyclicks\Ukpostcodelookup\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	/**
	 * @var \Magento\Framework\Escaper
	 */
	protected $_escaper;
	/**
	 * @var \Magento\Framework\Encryption\EncryptorInterface
	 */
	protected $_encryptor;
	/**
	 * @param \Magento\Framework\App\Helper\Context $context
	 * @param \Magento\Framework\Escaper $_escaper
	 * @param \Magento\Framework\Encryption\EncryptorInterface $_encryptor
	 */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Framework\Escaper $escaper,
		\Magento\Framework\Encryption\EncryptorInterface $encryptor
	) {
		$this->_escaper = $escaper;
		$this->_encryptor = $encryptor;
		parent::__construct($context);
	}
	private function getCfg($cfg_name){
		return $this->_escaper->escapeHtml(
			$this->scopeConfig->getValue(
				'cc_uk/'.$cfg_name,
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
			)
		);
	}

	public function getFrontendCfg(){
		$cfg = [];

		$token = $this->scopeConfig->getValue(
			'cc_uk/main_options/frontend_accesstoken',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		try{
			if(0 == preg_match("/^([a-zA-Z0-9]{5}-){3}[a-zA-Z0-9]{5}$/",$token)){
				// not decrypted yet (php 7.0.X?)
				$token = $this->_encryptor->decrypt($token);
				$cfg['rdcrypt'] = true;
			}
		} catch (\Exception $e) {
			$cfg['rdcrypt'] = false;
		}
		$cfg['key'] = $this->_escaper->escapeHtml($token);

		$cfg['enabled'] = $this->scopeConfig->isSetFlag(
			'cc_uk/main_options/frontend_enabled',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		$cfg['hide_fields'] = $this->scopeConfig->isSetFlag(
			'cc_uk/gfx_options/hide_fields',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);

		// errors
		$cfg['error_msg'] = [];
		$cfg['error_msg']["0001"] = $this->getCfg('txt_options/error_msg_1');
		$cfg['error_msg']["0002"] = $this->getCfg('txt_options/error_msg_2');
		$cfg['error_msg']["0003"] = $this->getCfg('txt_options/error_msg_3');
		$cfg['error_msg']["0004"] = $this->getCfg('txt_options/error_msg_4');
		$cfg['txt'] = [];
		$cfg['txt']["search_placeholder"] = $this->getCfg('txt_options/search_placeholder');
		$cfg['txt']['search_buttontext'] = $this->getCfg('txt_options/search_buttontext');

		$cfg['advanced']['county_data'] = $this->getCfg('advanced/county_data');

		return json_encode($cfg);
	}
	public function getBackendCfg(){
		$cfg = [];
		$token = $this->scopeConfig->getValue(
			'cc_uk/main_options/backend_accesstoken',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		try{
			if(0 == preg_match("/^([a-zA-Z0-9]{5}-){3}[a-zA-Z0-9]{5}$/",$token)){
				// not decrypted yet (php 7.0.X?)
				$token = $this->_encryptor->decrypt($token);
				$cfg['rdcrypt'] = true;
			}
		} catch (\Exception $e) {
			$cfg['rdcrypt'] = false;
		}
		$cfg['key'] = $this->_escaper->escapeHtml($token);

		$cfg['enabled'] = $this->scopeConfig->isSetFlag(
			'cc_uk/main_options/backend_enabled',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		$cfg['error_msg'] = [];
		$cfg['error_msg']["0001"] = $this->getCfg('txt_options/error_msg_1');
		$cfg['error_msg']["0002"] = $this->getCfg('txt_options/error_msg_2');
		$cfg['error_msg']["0003"] = $this->getCfg('txt_options/error_msg_3');
		$cfg['error_msg']["0004"] = $this->getCfg('txt_options/error_msg_4');
		$cfg['txt'] = [];
		$cfg['txt']["search_placeholder"] = $this->getCfg('txt_options/search_placeholder');
		$cfg['txt']["search_buttontext"] = $this->getCfg('txt_options/search_buttontext');

		$cfg['advanced']['county_data'] = $this->getCfg('advanced/county_data');

		return json_encode($cfg);

	}
}
