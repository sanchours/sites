/**
 * @description Создание нового статуса
 * @step Переходим на закладку заказы
 * @step Переходим в статусы и добавляем статус
 * @step Возвращаемся на страницу заказов
 */
import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import com.kms.katalon.core.checkpoint.Checkpoint as Checkpoint
import com.kms.katalon.core.checkpoint.CheckpointFactory as CheckpointFactory
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as MobileBuiltInKeywords
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as Mobile
import com.kms.katalon.core.model.FailureHandling as FailureHandling
import com.kms.katalon.core.testcase.TestCase as TestCase
import com.kms.katalon.core.testcase.TestCaseFactory as TestCaseFactory
import com.kms.katalon.core.testdata.TestData as TestData
import com.kms.katalon.core.testdata.TestDataFactory as TestDataFactory
import com.kms.katalon.core.testobject.ObjectRepository as ObjectRepository
import com.kms.katalon.core.testobject.TestObject as TestObject
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WSBuiltInKeywords
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WS
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUiBuiltInKeywords
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import com.kms.katalon.core.exception.StepErrorException as StepErrorException
import internal.GlobalVariable as GlobalVariable
import org.openqa.selenium.Keys as Keys
import urlSite.BaseLink as BaseLink
import data.OrdersData as OrdersData

OrdersData DataOrders = new OrdersData()

Url1 = WebUI.getUrl()

if (Url1 != BaseLink.getUrlDef() + '/admin/#out.left.tools=Order;out.tabs=tools_Order') {
	WebUI.click(findTestObject('CMS/Navigate/span_control_panel'))

    WebUI.click(findTestObject('Object Repository/CMS/Orders/div_orders'))
} 

Url2 = WebUI.getUrl()

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersList/span_statuses_orders'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/StatusOrder/span_add_status_order'))

WebUI.setText(findTestObject('Object Repository/CMS/Orders/StatusOrder/input_name_new_status'), DataOrders.status)

WebUI.setText(findTestObject('Object Repository/CMS/Orders/StatusOrder/input_title_new_status'), DataOrders.titlestatus)

WebUI.click(findTestObject('Object Repository/CMS/Orders/StatusOrder/span_save_status_order'))

namestatus = WebUI.getText(findTestObject('Object Repository/CMS/Orders/StatusOrder/div_name_status_last'))

if (namestatus != DataOrders.status) {
	throw new StepErrorException('Имя не совпадает с шаблоном!')
}

titlestatus = WebUI.getText(findTestObject('Object Repository/CMS/Orders/StatusOrder/div_title_ststus_list'))

if (titlestatus != DataOrders.titlestatus) {
	throw new StepErrorException('Заголовок не совпадает с шаблоном!')
}

WebUI.click(findTestObject('CMS/Orders/span_back'))