/**
 * @description Проверка соответствия полей
 * @step Переход в оформление заказа
 * @step Заполнение полей имя, адрес, индекс, телефон, почта и согласия с политконф
 * @step Отправка заказа
 * @step Проверка присутствия заказа в админке
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
import data.OrdersData as OrdersData
import data.CatalogData as CatalogData

CatalogData DataCatalog = new CatalogData()
OrdersData DataOrders = new OrdersData()

namelist = WebUI.getText(findTestObject('Object Repository/CMS/Orders/OrdersList/div_name_first_order'))

if (namelist != DataOrders.name) {
	throw new StepErrorException('Имя не совпадает с шаблоном!')
}

maillist = WebUI.getText(findTestObject('Object Repository/CMS/Orders/OrdersList/div_mail_first_order'))

if (maillist != DataOrders.email) {
	throw new StepErrorException('Почта не совпадает с шаблоном!')
}

pricelist = WebUI.getText(findTestObject('Object Repository/CMS/Orders/OrdersList/div_price_first_order'))

if (pricelist != DataCatalog.price) {
	throw new StepErrorException('Цена не совпадает с шаблоном!')
}

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersList/img_edit_first_order'))
	
namedet = WebUI.getAttribute(findTestObject('Object Repository/CMS/Orders/DetailOrder/input_order_detail_person'), 'value')

if (namedet != DataOrders.name) {
	throw new StepErrorException('Имя не совпадает с шаблоном!')
}

postdet = WebUI.getAttribute(findTestObject('Object Repository/CMS/Orders/DetailOrder/input_order_detail_postcode'), 'value')

if (postdet != DataOrders.postcode) {
	throw new StepErrorException('Индекс не совпадает с шаблоном!')
}

addressdet = WebUI.getAttribute(findTestObject('Object Repository/CMS/Orders/DetailOrder/input_order_detail_address'), 'value')

if (addressdet != DataOrders.address) {
	throw new StepErrorException('Адрес не совпадает с шаблоном!')
}

teldet = WebUI.getAttribute(findTestObject('Object Repository/CMS/Orders/DetailOrder/input_order_detail_phone'), 'value')

if (teldet != DataOrders.telcont) {
	throw new StepErrorException('Телефон не совпадает с шаблоном!')
}

maildet = WebUI.getAttribute(findTestObject('Object Repository/CMS/Orders/DetailOrder/input_order_detail_mail'), 'value')

if (maildet != DataOrders.email) {
	throw new StepErrorException('Почта не совпадает с шаблоном!')
}

textdet = WebUI.getAttribute(findTestObject('Object Repository/CMS/Orders/DetailOrder/textarea_order_detail_wishes'), 'value')

if (textdet != DataOrders.text) {
	throw new StepErrorException('Текст пожелания не совпадает с шаблоном!')
}

WebUI.click(findTestObject('Object Repository/CMS/Orders/span_back'))
