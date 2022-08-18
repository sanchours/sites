/**
 * @description Изменение писем клиентам
 * @step Переходим в раздел клиенты
 * @step Переходим в раздел изменение писем и меняем тест и название письма, сохраняем изменение
 * @step Проверяем сохранение изменений писем
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
import org.openqa.selenium.Keys as Keys
import data.OrdersData as OrdersData
import com.kms.katalon.core.exception.StepErrorException as StepErrorException

OrdersData DataOrders = new OrdersData()

WebUI.click(findTestObject('CMS/Navigate/span_control_panel'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/div_clients'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ClientList/span_letter_editor'))

WebUI.setText(findTestObject('Object Repository/CMS/Clients/LettersClient/input_letter_title_client'), '1')

WebUI.setText(findTestObject('Object Repository/CMS/Clients/LettersClient/body_letter_client'), '2')

WebUI.click(findTestObject('Object Repository/CMS/Clients/LettersClient/span_save_letter_client'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ClientList/span_letter_editor'))

title = WebUI.getAttribute(findTestObject('Object Repository/CMS/Clients/LettersClient/input_letter_title_client'), 'value')

if (title != '1'){
	throw new StepErrorException('Заголовок не поменялся!')
}

text = WebUI.getText(findTestObject('Object Repository/CMS/Clients/LettersClient/body_letter_client'))

if (text != '2'){
	throw new StepErrorException('Текст письма не поменялся!')
} 