/**
 * @description Добавления второго клиента
 * @step Переход в раздел клиенты
 * @step Создание нового клиента и заполнение данными клиента
 * @step Сохранение клиента
 */
import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import com.kms.katalon.core.checkpoint.Checkpoint as Checkpoint
import com.kms.katalon.core.cucumber.keyword.CucumberBuiltinKeywords as CucumberKW
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as Mobile
import com.kms.katalon.core.model.FailureHandling as FailureHandling
import com.kms.katalon.core.testcase.TestCase as TestCase
import com.kms.katalon.core.testdata.TestData as TestData
import com.kms.katalon.core.testobject.TestObject as TestObject
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WS
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import internal.GlobalVariable as GlobalVariable
import data.OrdersData as OrdersData
import urlSite.BaseLink as BaseLink

OrdersData DataOrders = new OrdersData()

if (WebUI.getUrl() != BaseLink.getUrlDef() + '/admin/#out.left.tools=Auth'){
	WebUI.click(findTestObject('CMS/Navigate/span_control_panel'))

	WebUI.click(findTestObject('Object Repository/CMS/Clients/div_clients'))
}

WebUI.delay(2)

WebUI.click(findTestObject('Object Repository/CMS/Clients/CreateClient/span_add_new_client'))

WebUI.setText(findTestObject('Object Repository/CMS/Clients/CreateClient/input_client_email'), DataOrders.emailsec)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/CreateClient/input_client_pass'), DataOrders.pass)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/CreateClient/input_client_name'), DataOrders.namesec)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/CreateClient/input_client_phone'), DataOrders.telsec)

WebUI.click(findTestObject('Object Repository/CMS/Clients/CreateClient/button_save_client'))

WebUI.delay(3)

