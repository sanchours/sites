/**
 * @description Изменение максимального кол-ва товара в заказе
 * @step Переход в настройки заказов
 * @step Изменение кол-ва максимального кол-ва товара в заказе
 * @step Сохранение изменений
 * @step Переход в Разделы
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

WebUI.click(findTestObject('CMS/Navigate/span_control_panel'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/div_orders'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersList/span_setting_orders'))

WebUI.setText(findTestObject('Object Repository/CMS/Orders/SettingsOrders/input_order_max_size'), '1')

WebUI.setText(findTestObject('Object Repository/CMS/Orders/SettingsOrders/input_onpage_orders_cms'), '2')

WebUI.setText(findTestObject('Object Repository/CMS/Orders/SettingsOrders/input_onpage_profile'), '2')

WebUI.setText(findTestObject('Object Repository/CMS/Orders/SettingsOrders/input_onpage_goods'), '1')

WebUI.click(findTestObject('Object Repository/CMS/Orders/SettingsOrders/span_save_setting_orders'))

WebUI.click(findTestObject('Object Repository/CMS/Banner/DisplaySliderSection/div_sections'))

