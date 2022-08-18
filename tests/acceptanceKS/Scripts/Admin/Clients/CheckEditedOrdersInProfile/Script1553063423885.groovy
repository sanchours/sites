/**
 * @description Проверка изменения заказа в ЛК
 * @step Переходим на детальную заказа
 * @step Переходим на редактирование заказа, меняем кол-во товара,цену и добавляем новый товар и возвращаемя на детальную
 * @step Меняем значения в полях заказа, сохраняем измененя
 * @step Переходим на детальную заказа в ЛК на лицевой части и проверяем поля на соответствие
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
import data.CatalogData as CatalogData
import data.OrdersData as OrdersData
import com.kms.katalon.core.exception.StepErrorException as StepErrorException

CatalogData DataCatalog = new CatalogData()

OrdersData DataOrders = new OrdersData()

DataOrders.setChangeData()

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersList/img_edit_first_order'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/span_orders_editor'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/span_add_goods_list'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/img_add_in_order_last_goods'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/div_count_first_orders_editor'))

WebUI.setText(findTestObject('Object Repository/CMS/Orders/OrdersEditor/input_count_order_editor'), '2')

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/div_name_first_orders_editor'))

WebUI.delay(2)

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/div_price_first_orders_editor'))

WebUI.setText(findTestObject('Object Repository/CMS/Orders/OrdersEditor/input_price_order_editor'), DataCatalog.pricesec)

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/div_name_first_orders_editor'))

WebUI.delay(2)

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersEditor/span_cancel_order_editor'))

WebUI.setText(findTestObject('Object Repository/CMS/Orders/DetailOrder/input_order_detail_person'), DataOrders.name)

WebUI.setText(findTestObject('Object Repository/CMS/Orders/DetailOrder/input_order_detail_postcode'), DataOrders.postcode)

WebUI.setText(findTestObject('Object Repository/CMS/Orders/DetailOrder/input_order_detail_address'), DataOrders.address)

WebUI.setText(findTestObject('Object Repository/CMS/Orders/DetailOrder/input_order_detail_phone'), DataOrders.tel)

WebUI.setText(findTestObject('Object Repository/CMS/Orders/DetailOrder/input_order_detail_mail'), DataOrders.email)

WebUI.setText(findTestObject('Object Repository/CMS/Orders/DetailOrder/textarea_order_detail_wishes'), DataOrders.text)

WebUI.takeScreenshot()

WebUI.click(findTestObject('Object Repository/CMS/Orders/DetailOrder/span_save_order'))

WebUI.switchToWindowIndex(1)

WebUI.click(findTestObject('Object Repository/CMS/Clients/ProfileClient/a_road_to_profile'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ProfileClient/a_road_detail_order_profile'))

WebUI.verifyElementPresent(findTestObject('Object Repository/CMS/Clients/ProfileClient/a_name_second_goods'), 3)

if (WebUI.getText(findTestObject('Object Repository/CMS/Clients/ProfileClient/a_name_goods')) != DataCatalog.itemname){
	throw new StepErrorException('Имя товара на детальной не соответстует!')
}

if (WebUI.getText(findTestObject('Object Repository/CMS/Clients/ProfileClient/strong_goods_price')) != DataCatalog.pricesec){
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

WebUI.takeScreenshot()
