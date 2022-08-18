/**
 * @description Проерка кол-ва заказов в CMS
 * @step Производим еще один заказ
 * @step Проверяем кол-во заказов на странице
 * @step Меняем кол-во выводимых заказов в CMS и проверяем кол-во заказов на странице
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
import urlSite.BaseLink as BaseLink
import data.OrdersData as OrdersData
import data.CatalogData as CatalogData
import com.kms.katalon.core.exception.StepErrorException as StepErrorException

CatalogData DataCatalog = new CatalogData()

OrdersData DataOrders = new OrdersData()

WebUI.switchToWindowIndex(1)

WebUI.navigateToUrl(BaseLink.getUrlDef() + '/' + DataCatalog.aliassection)

WebUI.click(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/button_buy_first_in_list'))

WebUI.click(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/button_road_to_cart'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/Checkout/a_order_checkout'))

WebUI.setText(findTestObject('Object Repository/CMS/Orders/Checkout/input_order_site_address'), DataOrders.address)

WebUI.setText(findTestObject('Object Repository/CMS/Orders/Checkout/input_order_site_name'), DataOrders.name)

WebUI.setText(findTestObject('Object Repository/CMS/Orders/Checkout/input_order_site_phone'), DataOrders.tel)

WebUI.setText(findTestObject('Object Repository/CMS/Orders/Checkout/input_order_site_postcode'), DataOrders.postcode)

WebUI.setText(findTestObject('Object Repository/CMS/Orders/Checkout/input_order_site_email'), DataOrders.email)

WebUI.setText(findTestObject('Object Repository/CMS/Orders/Checkout/textarea_order_site_wishes'), DataOrders.text)

WebUI.click(findTestObject('Object Repository/CMS/Orders/Checkout/inpur_order_checkbox_trigger'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/Checkout/button_confirmation_order_cart'))

WebUI.switchToWindowIndex(0)

WebUI.refresh()

WebUI.verifyElementPresent(findTestObject('Object Repository/CMS/Orders/OrdersList/div_second_order_list'), 5)

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersList/span_setting_orders'))

WebUI.setText(findTestObject('Object Repository/CMS/Orders/SettingsOrders/input_onpage_orders_cms'), '1')

WebUI.click(findTestObject('Object Repository/CMS/Orders/SettingsOrders/span_save_setting_orders'))

WebUI.verifyElementNotPresent(findTestObject('Object Repository/CMS/Orders/OrdersList/div_second_order_list'), 5)

id = WebUI.getText(findTestObject('Object Repository/CMS/Orders/OrdersList/div_id_first_order_list'))

numpage = WebUI.getAttribute(findTestObject('Object Repository/CMS/Orders/OrdersList/input_number_page_orders_list'), 'value')

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersList/span_next_page_orders_adm'))

WebUI.delay(1)

if (WebUI.getText(findTestObject('Object Repository/CMS/Orders/OrdersList/div_id_first_order_list')) == id){
	throw new StepErrorException('id заказа на второй странице совпадает с новером заказа на первой странице!')
}

if (WebUI.getAttribute(findTestObject('Object Repository/CMS/Orders/OrdersList/input_number_page_orders_list'), 'value') == numpage){
	throw new StepErrorException('Номер странице не поменялся!')
}