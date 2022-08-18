/**
 * @description Проверка изменения статуса заказа
 * @step Смотрим статус у первого заказа
 * @step Переходим на детальную заказа и смотрим статус заказа
 * @step Меняем статус заказа на предпоследний и сохраняем
 * @step Смотрим статус у первого заказа
 * @step Переходим на детальную заказа и смотрим статус заказа
 * @step Меняем статус заказа на предпоследний и сохраняем
 * @step Смотрим статус у первого заказа
 * @step Переходим на детальную заказа и смотрим статус заказа
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

orderstatus1 = WebUI.getText(findTestObject('Object Repository/CMS/Orders/OrdersList/div_status_first_order'))

println(orderstatus1)

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersList/img_edit_first_order'))

orderstatusdet1 = WebUI.getAttribute(findTestObject('Object Repository/CMS/Orders/DetailOrder/input_order_detail_status'), 'value')

if (orderstatusdet1 != orderstatus1) {
	throw new StepErrorException('Статусы на списковой и детальной не совпадают!')
}

WebUI.click(findTestObject('Object Repository/CMS/Orders/DetailOrder/div_list_type_status'))

orderstatus2 = WebUI.getText(findTestObject('Object Repository/CMS/Orders/DetailOrder/li_penultimate_status_list'))

println(orderstatus2)

WebUI.click(findTestObject('Object Repository/CMS/Orders/DetailOrder/li_penultimate_status_list'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/DetailOrder/span_save_order'))

orderstatus2list = WebUI.getText(findTestObject('Object Repository/CMS/Orders/OrdersList/div_status_first_order'))

println(orderstatus2list)

if (orderstatus2list != orderstatus2) {
	throw new StepErrorException('Статусы на списковой не сообветствует проставленному на детальной!')
}

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersList/img_edit_first_order'))

orderstatusdet2 = WebUI.getAttribute(findTestObject('Object Repository/CMS/Orders/DetailOrder/input_order_detail_status'), 'value')

if (orderstatusdet2 != orderstatus2) {
	throw new StepErrorException('Статус на детальной после изменения не сохранился!')
}

WebUI.click(findTestObject('Object Repository/CMS/Orders/DetailOrder/div_list_type_status'))

orderstatus3 = WebUI.getText(findTestObject('Object Repository/CMS/Orders/DetailOrder/li_last_satus_list'))

println(orderstatus3)

if (orderstatus3 != DataOrders.titlestatus) {
	throw new StepErrorException('Название добавленного статуса не соотсествует заданному!')
}

WebUI.click(findTestObject('Object Repository/CMS/Orders/DetailOrder/li_last_satus_list'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/DetailOrder/span_save_order'))

orderstatus3list = WebUI.getText(findTestObject('Object Repository/CMS/Orders/OrdersList/div_status_first_order'))

println(orderstatus3list)

if (orderstatus3list != DataOrders.titlestatus) {
	throw new StepErrorException('Статусы на списковой не сообветствует проставленному на детальной!')
}

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersList/img_edit_first_order'))

orderstatusdet3 = WebUI.getAttribute(findTestObject('Object Repository/CMS/Orders/DetailOrder/input_order_detail_status'), 'value')

if (orderstatusdet3 != DataOrders.titlestatus) {
	throw new StepErrorException('Статус на детальной после изменения не сохранился!')
}

secondstat = WebUI.getText(findTestObject('Object Repository/CMS/Orders/StatusOrder/div_second_status_history'))

println(secondstat)

if (secondstat != orderstatus2) {
	throw new StepErrorException('В истории изменения статусов не верно стоит статус первого изменения!')
}

thirdstat = WebUI.getText(findTestObject('Object Repository/CMS/Orders/StatusOrder/div_third_status_history'))

println(thirdstat)

if (thirdstat != orderstatus3) {
	throw new StepErrorException('В истории изменения статусов не верно стоит статус второго изменения!')
}

WebUI.verifyElementNotPresent(findTestObject('Object Repository/CMS/Orders/StatusOrder/div_fourth_status_history'), 1)



