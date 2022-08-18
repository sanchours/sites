/**
 * @description Проверка полей в заказе
 * @step Переходим на детальную заказа
 * @step Проверяем соответствие полей
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
import com.kms.katalon.core.exception.StepErrorException as StepErrorException

OrdersData DataOrders = new OrdersData()

WebUI.click(findTestObject('Object Repository/CMS/Orders/Checkout/a_order_checkout'))

name = WebUI.getAttribute(findTestObject('Object Repository/CMS/Orders/Checkout/input_order_site_name'), 'value')

if (name != DataOrders.name){
	throw new StepErrorException('Имя в заказе не соответствует!')
}

postcode = WebUI.getAttribute(findTestObject('Object Repository/CMS/Orders/Checkout/input_order_site_postcode'), 'value')

if (postcode != DataOrders.postcode){
	throw new StepErrorException('Индекс в заказе не соответствует!')
}

address = WebUI.getAttribute(findTestObject('Object Repository/CMS/Orders/Checkout/input_order_site_address'), 'value')

if (address != DataOrders.address){
	throw new StepErrorException('Адрес в заказе не соответствует!')
}

tel = WebUI.getAttribute(findTestObject('Object Repository/CMS/Orders/Checkout/input_order_site_phone'), 'value')

if (tel != DataOrders.telcont){
	throw new StepErrorException('Телефон в заказе не соответствует!')
}

email = WebUI.getAttribute(findTestObject('Object Repository/CMS/Orders/Checkout/input_order_site_email'), 'value')

if (email != DataOrders.email){
	throw new StepErrorException('Телефон в заказе не соответствует!')
}



