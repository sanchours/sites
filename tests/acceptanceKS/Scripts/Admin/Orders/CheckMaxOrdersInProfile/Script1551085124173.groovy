/**
 * @description Проерка кол-ва заказов в ЛК
 * @step Переходим в ЛК и проверяем наличие второго заказа и отсутствие постраничника
 * @step Переходим в админку и меняем кол-во выводимых заказов в ЛК
 * @step Возвращаемся в ЛК и проверяем кол-во заказов на странице
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
import com.kms.katalon.core.exception.StepErrorException as StepErrorException

WebUI.switchToWindowIndex(1)

WebUI.click(findTestObject('Object Repository/CMS/Clients/ProfileClient/a_road_to_profile'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ProfileClient/li_profile_orders'))

WebUI.verifyElementNotPresent(findTestObject('Object Repository/CMS/Clients/ProfileClient/div_pageline_profile'), 1)

WebUI.verifyElementPresent(findTestObject('Object Repository/CMS/Clients/ProfileClient/div_second_order_profile'), 1)

WebUI.switchToWindowIndex(0)

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersList/span_setting_orders'))

WebUI.setText(findTestObject('Object Repository/CMS/Orders/SettingsOrders/input_onpage_profile'), '1')

WebUI.click(findTestObject('Object Repository/CMS/Orders/SettingsOrders/span_save_setting_orders'))

WebUI.switchToWindowIndex(1)

WebUI.refresh()

WebUI.verifyElementPresent(findTestObject('Object Repository/CMS/Clients/ProfileClient/div_pageline_profile'), 1)

WebUI.verifyElementNotPresent(findTestObject('Object Repository/CMS/Clients/ProfileClient/div_second_order_profile'), 1)

href = WebUI.getAttribute(findTestObject('Object Repository/CMS/Clients/ProfileClient/div_number_orders_site'), 'href')

url = WebUI.getUrl()

WebUI.click(findTestObject('Object Repository/CMS/Clients/ProfileClient/a_second_page_orders_profile'))

if (WebUI.getUrl() == url){
	throw new StepErrorException('Переход по пагинатору не прошел!')
}

if (WebUI.getAttribute(findTestObject('Object Repository/CMS/Clients/ProfileClient/div_number_orders_site'), 'href') == href){
	throw new StepErrorException('На второй страницы заказ аналогичный заказу на первой странице!')
}




