/**
 * @description Отправка заказа клиентом
 * @step Переход в оформление заказа
 * @step Заполнение полей имя, адрес, индекс, телефон и согласия с политконф
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
import internal.GlobalVariable as GlobalVariable
import org.openqa.selenium.Keys as Keys
import data.OrdersData as OrdersData

OrdersData DataOrders = new OrdersData()

WebUI.click(findTestObject('Object Repository/CMS/Orders/Checkout/a_order_checkout'))

WebUI.setText(findTestObject('Object Repository/CMS/Orders/Checkout/input_order_site_address'), DataOrders.address)

WebUI.setText(findTestObject('Object Repository/CMS/Orders/Checkout/input_order_site_name'), DataOrders.name)

WebUI.setText(findTestObject('Object Repository/CMS/Orders/Checkout/input_order_site_phone'), DataOrders.tel)

WebUI.setText(findTestObject('Object Repository/CMS/Orders/Checkout/input_order_site_postcode'), DataOrders.postcode)

WebUI.setText(findTestObject('Object Repository/CMS/Orders/Checkout/textarea_order_site_wishes'), DataOrders.text)

WebUI.click(findTestObject('Object Repository/CMS/Orders/Checkout/inpur_order_checkbox_trigger'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/Checkout/button_confirmation_order_cart'))

WebUI.switchToWindowIndex(0)

WebUI.click(findTestObject('CMS/Navigate/span_control_panel'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/div_orders'))

WebUI.verifyElementPresent(findTestObject('Object Repository/CMS/Orders/OrdersList/div_first_order_list'), 1)