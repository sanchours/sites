/**
 * @description Проверка детальной заказа в ЛК
 * @step Переходим на детальную заказа в ЛК
 * @step Проверяем поля заказа на правильность отображения
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
import data.CatalogData as CatalogData

CatalogData DataCatalog = new CatalogData()

OrdersData DataOrders = new OrdersData()

WebUI.switchToWindowIndex(1)

WebUI.click(findTestObject('Object Repository/CMS/Clients/ProfileClient/a_road_to_profile'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ProfileClient/a_road_detail_order_profile'))

if (WebUI.getText(findTestObject('Object Repository/CMS/Clients/ProfileClient/a_name_goods')) != DataCatalog.itemname){
	throw new StepErrorException('Имя товара на детальной не соответстует!')
}

if (WebUI.getText(findTestObject('Object Repository/CMS/Clients/ProfileClient/strong_goods_price')) != DataCatalog.price){
	throw new StepErrorException('Цена товара на детальной не соответстует!')
}

if (WebUI.getText(findTestObject('Object Repository/CMS/Clients/ProfileClient/span_address_order_profile')) != DataOrders.address){
	throw new StepErrorException('Адрес пользователя на детальной не соответстует!')
}

if (WebUI.getText(findTestObject('Object Repository/CMS/Clients/ProfileClient/span_email_order_profile')) != DataOrders.email){
	throw new StepErrorException('Почта пользователя на детальной не соответстует!')
}

if (WebUI.getText(findTestObject('Object Repository/CMS/Clients/ProfileClient/span_name_order_profile')) != DataOrders.name){
	throw new StepErrorException('Имя пользователя на детальной не соответстует!')
}

if (WebUI.getText(findTestObject('Object Repository/CMS/Clients/ProfileClient/span_phone_order_profile')) != DataOrders.telcont){
	throw new StepErrorException('Телефон пользователя на детальной не соответстует!')
}

if (WebUI.getText(findTestObject('Object Repository/CMS/Clients/ProfileClient/span_postcode_order_profile')) != DataOrders.postcode){
	throw new StepErrorException('Индекс пользователя на детальной не соответстует!')
}




