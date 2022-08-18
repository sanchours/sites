/**
 * @description Изменение писем в заказе
 * @step Переходим в заказы 
 * @step меняем тест письма и проверяем его сохранение
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
import com.kms.katalon.core.exception.StepErrorException as StepErrorException
import internal.GlobalVariable as GlobalVariable
import data.OrdersData as OrdersData

OrdersData DataOrders = new OrdersData()

WebUI.click(findTestObject('CMS/Navigate/span_control_panel'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/div_orders'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersList/span_letter_editor_orders'))

WebUI.setText(findTestObject('Object Repository/CMS/Orders/OrdersLetters/input_title_letter'), DataOrders.lettettitle)

WebUI.setText(findTestObject('Object Repository/CMS/Orders/OrdersLetters/body_text_letter'), DataOrders.lettertext)

WebUI.setText(findTestObject('Object Repository/CMS/Orders/OrdersLetters/input_title_letter_adm'), '1')

WebUI.setText(findTestObject('Object Repository/CMS/Orders/OrdersLetters/body_text_letter_adm'), '2')

WebUI.setText(findTestObject('Object Repository/CMS/Orders/OrdersLetters/input_title_letters_change_status'), '3')

WebUI.setText(findTestObject('Object Repository/CMS/Orders/OrdersLetters/body_text_letters_change_status'), '4')

WebUI.setText(findTestObject('Object Repository/CMS/Orders/OrdersLetters/input_title_letters_change_order'), '5')

WebUI.setText(findTestObject('Object Repository/CMS/Orders/OrdersLetters/body_text_letters_change_order'), '6')

WebUI.setText(findTestObject('Object Repository/CMS/Orders/OrdersLetters/input_title_letters_status_paid'), '7')

WebUI.setText(findTestObject('Object Repository/CMS/Orders/OrdersLetters/body_text_letters_status_paid'), '8')

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersLetters/span_save_letters'))

WebUI.delay(3)

WebUI.click(findTestObject('Object Repository/CMS/Orders/span_back'))

WebUI.click(findTestObject('Object Repository/CMS/Orders/OrdersList/span_letter_editor_orders'))

lettertitle = WebUI.getAttribute(findTestObject('Object Repository/CMS/Orders/OrdersLetters/input_title_letter'), 'value')

if (lettertitle != DataOrders.lettettitle) {
	throw new StepErrorException('Заголовок не совпадает с шаблоном!')
}

lettertext = WebUI.getText(findTestObject('Object Repository/CMS/Orders/OrdersLetters/body_text_letter'))

if (lettertext != DataOrders.lettertext) {
	throw new StepErrorException('Текст письма не совпадает с шаблоном!')
}

if (WebUI.getAttribute(findTestObject('Object Repository/CMS/Orders/OrdersLetters/input_title_letter_adm'), 'value') != '1'){
	throw new StepErrorException('Заголовок письма админу не совпадает!')
}

if (WebUI.getText(findTestObject('Object Repository/CMS/Orders/OrdersLetters/body_text_letter_adm')) != '2'){
	throw new StepErrorException('Текст письма админу не совпадает!')
}

if (WebUI.getAttribute(findTestObject('Object Repository/CMS/Orders/OrdersLetters/input_title_letters_change_status'), 'value') != '3'){
	throw new StepErrorException('Заголовок письма об изменении статуса не совпадает!')
}

if (WebUI.getText(findTestObject('Object Repository/CMS/Orders/OrdersLetters/body_text_letters_change_status')) != '4'){
	throw new StepErrorException('Текст письма об изменении статуса не совпадает!')
}

if (WebUI.getAttribute(findTestObject('Object Repository/CMS/Orders/OrdersLetters/input_title_letters_change_order'), 'value') != '5'){
	throw new StepErrorException('Заголовок письма об изменении заказа не совпадает!')
}

if (WebUI.getText(findTestObject('Object Repository/CMS/Orders/OrdersLetters/body_text_letters_change_order')) != '6'){
	throw new StepErrorException('Текст письма об изменении заказа не совпадает!')
}
if (WebUI.getAttribute(findTestObject('Object Repository/CMS/Orders/OrdersLetters/input_title_letters_status_paid'), 'value') != '7'){
	throw new StepErrorException('Заголовок письма об оплате заказа не совпадает!')
}

if (WebUI.getText(findTestObject('Object Repository/CMS/Orders/OrdersLetters/body_text_letters_status_paid')) != '8'){
	throw new StepErrorException('Текст письма об оплате заказа не совпадает!')
}