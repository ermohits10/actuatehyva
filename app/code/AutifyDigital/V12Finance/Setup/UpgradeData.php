<?php
/*
 * This program is the CONFIDENTIAL and PROPRIETARY property of Autify digital Ltd.
 * Any unauthorized use, reproduction or transfer of this computer program is strictly prohibited.
 * Copyright (c) 2020-2022 Autify digital Ltd.
 * This is an unpublished work, and is subject to limited distribution and restricted disclosure only.
 * ALL RIGHTS RESERVED.
 *
 */

namespace AutifyDigital\V12Finance\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Email\Model\TemplateFactory;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Cms\Model\PageFactory;

/**
 * Class UpgradeData
 * @package AutifyDigital\V12Finance\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Template factory
     *
     * @var TemplateFactory
     */
    private $templateFactory;

    /**
     * StatusFactory
     * @var StatusFactory
     */
    private $statusFactory;

    private $pageFactory;

    /**
     * UpgradeData constructor.
     * @param TemplateFactory $templateFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory
     */
    public function __construct(
        TemplateFactory $templateFactory,
        StatusFactory $statusFactory,
        PageFactory $pageFactory
    ) {
        $this->templateFactory = $templateFactory;
        $this->statusFactory = $statusFactory;
        $this->pageFactory = $pageFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $data = '{{template config_path="design/email/header_template"}}
                <table>
                    <tr class="email-intro">
                        <td>
                            <p class="greeting">{{trans "Hello "}}{{var customer_name}}, </p>
                        </td>
                    </tr>
                    <tr class="email-summary">
                        <td>
                            <h1>{{trans \'Your V12 Application #\' }}{{var application_id}}{{trans \' for order #\'}}{{var order_id}}</h1>
                        </td>
                    </tr>
                    <tr class="email-information">
                        <td>
                            <p>
                                {{trans \'We have received your order. You are in the application \'}} {{var status}}.
                                {{trans \'The next step is to \'}}{{var status_message}}.
                                {{trans \'You are close to owning your order.\'}}
                            </p>
                            <p>
                                {{trans \'If you have questions about your order, you can email us at \'}}<a href="mailto:{{config path=\'trans_email/ident_general/email\'}}">{{config path="trans_email/ident_general/email"}}</a> {{trans \' or call us at\'}} <a href="tel:{{config path=\'general/store_information/phone\'}}">{{config path="general/store_information/phone"}}</a>
                            </p>
                        </td>
                    </tr>
                </table>
                {{template config_path="design/email/footer_template"}}';

            $template = $this->templateFactory->create();
            $template->setTemplateCode('V12 Finance Order Pending Email Template');
            $template->setTemplateText($data);
            $template->setTemplateType(\Magento\Email\Model\Template::TYPE_HTML);
            $template->setTemplateStyles('');
            $template->setTemplateSubject('{{trans "Your Application order notification"}}');
            $templateID = $template->save()->getId();

            $insertdata = [
                'scope' => 'default',
                'scope_id' => 0,
                'path' => 'autifydigital/v12finance/email_template',
                'value' => $templateID,
            ];
            $setup->getConnection()
                ->insertOnDuplicate($setup->getTable('core_config_data'), $insertdata, ['value']);

            $declineddata = '{{template config_path="design/email/header_template"}}
                <table>
                    <tr class="email-intro">
                        <td>
                            <p class="greeting">{{trans \'Hello \' }} {{var customer_name}},</p>
                        </td>
                    </tr>
                    <tr class="email-summary">
                        <td>
                            <h1>{{trans \'Your V12 Application\' }} {{var application_id}} {{trans \' for Order: #\'}}{{var order_id}}</h1>
                        </td>
                    </tr>
                    <tr class="email-information">
                        <td>
                            <p>
                                {{trans \'Sorry to know that the finance application was declined by the provider. Do you want to continue the purchase of your order using another payment?\'}}
                            </p>
                            <p>
                                {{trans \'If you have questions about your order, you can email us at \'}}<a href="mailto:{{config path=\'trans_email/ident_general/email\'}}">{{config path="trans_email/ident_general/email"}}</a> {{trans \' or call us at\'}} <a href="tel:{{config path=\'general/store_information/phone\'}}">{{config path="general/store_information/phone"}}</a>
                            </p>
                        </td>
                    </tr>
                </table>
                {{template config_path="design/email/footer_template"}}';

            $declinedTemplateFactory = $this->templateFactory->create();
            $declinedTemplateFactory->setTemplateCode('V12 Finance Order Declined Email Template');
            $declinedTemplateFactory->setTemplateText($declineddata);
            $declinedTemplateFactory->setTemplateType(\Magento\Email\Model\Template::TYPE_HTML);
            $declinedTemplateFactory->setTemplateStyles('');
            $declinedTemplateFactory->setTemplateSubject('{{trans "Your Declined Application Order notification"}}');
            $templaDeclinedID = $declinedTemplateFactory->save()->getId();

            $insertdeclineddata = [
                'scope' => 'default',
                'scope_id' => 0,
                'path' => 'autifydigital/v12finance/declinedemail_template',
                'value' => $templaDeclinedID,
            ];
            $setup->getConnection()
                ->insertOnDuplicate($setup->getTable('core_config_data'), $insertdeclineddata, ['value']);

            $this->addOrderStatus();
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $cmsPageData = [
                'title' => 'V12 Finance',
                'page_layout' => '1column',
                'identifier' => 'v12finance',
                'content' => "<h1>V12 Finance</h1> <p>V12 Retail Finance requires the merchant to have a public facing page explaining different finance options on the website such as Finance plan example, FAQs etc. Our module creates a basic page where the merchant can add the information. For more information, please contact your V12 Retail finance account manager.</p>",
                'is_active' => 1,
                'stores' => [0],
                'sort_order' => 0
            ];

            // create page
            $this->pageFactory->create()->setData($cmsPageData)->save();
        }

        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $awaitingcontract_template = '{{template config_path="design/email/header_template"}}
                <table>
                    <tr class="email-intro">
                        <td>
                            <p class="greeting">{{trans "Hello "}}{{var customer_name}}, </p>
                        </td>
                    </tr>
                    <tr class="email-summary">
                        <td>
                            <h1>{{trans \'Your V12 Application #\' }}{{var application_id}}{{trans \' for order #\'}}{{var order_id}}</h1>
                        </td>
                    </tr>
                    <tr class="email-information">
                        <td>
                            <p>
                                {{trans \'The last step is to sign the application, and we will process your order. \'}}
                            </p>
                            <p>
                                {{trans \'If you have questions about your order, you can email us at \'}}<a href="mailto:{{config path=\'trans_email/ident_general/email\'}}">{{config path="trans_email/ident_general/email"}}</a> {{trans \' or call us at\'}} <a href="tel:{{config path=\'general/store_information/phone\'}}">{{config path="general/store_information/phone"}}</a>
                            </p>
                        </td>
                    </tr>
                </table>
                {{template config_path="design/email/footer_template"}}';

            $template = $this->templateFactory->create();
            $template->setTemplateCode('V12 Finance Awaiting Contract Email Template');
            $template->setTemplateText($awaitingcontract_template);
            $template->setTemplateType(\Magento\Email\Model\Template::TYPE_HTML);
            $template->setTemplateStyles('');
            $template->setTemplateSubject('{{trans "Your Application notification"}}');
            $templateID = $template->save()->getId();

            $insertdata = [
                'scope' => 'default',
                'scope_id' => 0,
                'path' => 'autifydigital/v12finance/awaitingcontract_template',
                'value' => $templateID,
            ];
            $setup->getConnection()
                ->insertOnDuplicate($setup->getTable('core_config_data'), $insertdata, ['value']);
        }

        if (version_compare($context->getVersion(), '1.10.0', '<')) {
            $errorEmailData = '{{template config_path="design/email/header_template"}}
                <table>
                    <tr class="email-intro">
                        <td>
                            <p class="greeting">{{trans \'Hello \' }} {{var customer_name}},</p>
                        </td>
                    </tr>
                    <tr class="email-summary">
                        <td>
                            <h1>{{trans \'Your V12 Application\' }} {{var application_id}} {{trans \' for Order: #\'}}{{var order_id}}</h1>
                        </td>
                    </tr>
                    <tr class="email-information">
                        <td>
                            <p>
                                {{trans \'Sorry to know that the finance application has an error. Do you want to continue the purchase of your order using another payment?\'}}
                            </p>
                            <p>
                                {{trans \'If you have questions about your order, you can email us at \'}}<a href="mailto:{{config path=\'trans_email/ident_general/email\'}}">{{config path="trans_email/ident_general/email"}}</a> {{trans \' or call us at\'}} <a href="tel:{{config path=\'general/store_information/phone\'}}">{{config path="general/store_information/phone"}}</a>
                            </p>
                        </td>
                    </tr>
                </table>
                {{template config_path="design/email/footer_template"}}';

            $errorTemplateFactory = $this->templateFactory->create();
            $errorTemplateFactory->setTemplateCode('V12 Finance Order Error Email Template');
            $errorTemplateFactory->setTemplateText($errorEmailData);
            $errorTemplateFactory->setTemplateType(\Magento\Email\Model\Template::TYPE_HTML);
            $errorTemplateFactory->setTemplateStyles('');
            $errorTemplateFactory->setTemplateSubject('{{trans "Your Error Application Order notification"}}');
            $templaErrorTemplateId = $errorTemplateFactory->save()->getId();

            $inserterrordata = [
                'scope' => 'default',
                'scope_id' => 0,
                'path' => 'autifydigital/v12finance/erroremail_template',
                'value' => $templaErrorTemplateId,
            ];
            $setup->getConnection()
                ->insertOnDuplicate($setup->getTable('core_config_data'), $inserterrordata, ['value']);
        }
        if (version_compare($context->getVersion(), '1.10.1', '<')) {
            $cancelledEmailData = '{{template config_path="design/email/header_template"}}
                <table>
                    <tr class="email-intro">
                        <td>
                            <p class="greeting">{{trans \'Hello \' }} {{var customer_name}},</p>
                        </td>
                    </tr>
                    <tr class="email-summary">
                        <td>
                            <h1>{{trans \'Your V12 Application\' }} {{var application_id}} {{trans \' for Order: #\'}}{{var order_id}}</h1>
                        </td>
                    </tr>
                    <tr class="email-information">
                        <td>
                            <p>
                                {{trans \'Sorry to know that the finance application was cancelled by the provider. Do you want to continue the purchase of your order using another payment?\'}}
                            </p>
                            <p>
                                {{trans \'If you have questions about your order, you can email us at \'}}<a href="mailto:{{config path=\'trans_email/ident_general/email\'}}">{{config path="trans_email/ident_general/email"}}</a> {{trans \' or call us at\'}} <a href="tel:{{config path=\'general/store_information/phone\'}}">{{config path="general/store_information/phone"}}</a>
                            </p>
                        </td>
                    </tr>
                </table>
                {{template config_path="design/email/footer_template"}}';

            $cancelledTemplateFactory = $this->templateFactory->create();
            $cancelledTemplateFactory->setTemplateCode('V12 Finance Order Canceled Email Template');
            $cancelledTemplateFactory->setTemplateText($cancelledEmailData);
            $cancelledTemplateFactory->setTemplateType(\Magento\Email\Model\Template::TYPE_HTML);
            $cancelledTemplateFactory->setTemplateStyles('');
            $cancelledTemplateFactory->setTemplateSubject('{{trans "Your Canceled Application Order notification"}}');
            $templaCanceledTemplateId = $cancelledTemplateFactory->save()->getId();

            $insertcanceleddata = [
                'scope' => 'default',
                'scope_id' => 0,
                'path' => 'autifydigital/v12finance/cancelledemail_template',
                'value' => $templaCanceledTemplateId,
            ];
            $setup->getConnection()
                ->insertOnDuplicate($setup->getTable('core_config_data'), $insertcanceleddata, ['value']);
        }
    }

    /**
     * Save Information
     */
    public function addOrderStatus()
    {
        $statusesArray = [
            ['finance_error', 'canceled', __('Finance Error')],
            ['finance_acknowledged', 'pending', __('Finance Acknowledged')],
            ['finance_referred', 'pending', __('Finance Referred')],
            ['finance_declined', 'canceled', __('Finance Declined')],
            ['finance_accepted', 'pending', __('Finance Accepted')],
            ['finance_awaitingfulfilment', 'processing', __('Finance Awaiting Fulfilment')],
            ['finance_cancelled', 'canceled', __('Finance Cancelled')]
        ];

        foreach ($statusesArray as $statuses) {
            $status = $this->statusFactory->create();
            $status->setData('status', $statuses[0]);
            $status->setData('label', $statuses[2]);
            $status->save();
            $status->assignState($statuses[1], false, true);
        }

    }

}
