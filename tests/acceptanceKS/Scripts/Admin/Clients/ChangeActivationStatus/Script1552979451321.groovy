/**
 * @description Изменение статуса активации клиента
 * @step Переход в раздел клиенты
 * @step Переход в меню изменения статуса, выбор третьего способа авторизации в списке и сохранение
 * @step Переход в меню изменения статуса, проверка что выбраный нами статус сохранился, выбор второго способа авторизации в списке и сохранение
 * @step Переход в меню изменения статуса, проверка что выбраный нами статус сохранился, выбор первого способа авторизации в списке и сохранение
 * @step Переход в меню изменения статуса, проверка что выбраный нами статус сохранился, выбор второго способа авторизации в списке и выход без сохранения
 * @step Переход в меню изменения статуса, проверка что выбраный нами статус не сохранился
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

WebUI.click(findTestObject('CMS/Navigate/span_control_panel'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/div_clients'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ClientList/span_activation_status'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ActivationStatus/div_list_activation_status'))

actstat = WebUI.getText(findTestObject('Object Repository/CMS/Clients/ActivationStatus/li_third_activation_status'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ActivationStatus/li_third_activation_status'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ActivationStatus/span_save_activation_status'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ClientList/span_activation_status'))

checkactstst = WebUI.getAttribute(findTestObject('Object Repository/CMS/Clients/ActivationStatus/input_activation_status'), 'value')

if (checkactstst != actstat) {
	throw new StepErrorException('Статус активации не сохранился!')
}

WebUI.click(findTestObject('Object Repository/CMS/Clients/ActivationStatus/div_list_activation_status'))

actstat = WebUI.getText(findTestObject('Object Repository/CMS/Clients/ActivationStatus/li_secont_activation_status'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ActivationStatus/li_secont_activation_status'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ActivationStatus/span_save_activation_status'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ClientList/span_activation_status'))

checkactstst = WebUI.getAttribute(findTestObject('Object Repository/CMS/Clients/ActivationStatus/input_activation_status'), 'value')

if (checkactstst != actstat) {
	throw new StepErrorException('Статус активации не сохранился на второй итерации!')
}

WebUI.click(findTestObject('Object Repository/CMS/Clients/ActivationStatus/div_list_activation_status'))

actstat = WebUI.getText(findTestObject('Object Repository/CMS/Clients/ActivationStatus/li_first_activation_status'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ActivationStatus/li_first_activation_status'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ActivationStatus/span_save_activation_status'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ClientList/span_activation_status'))

checkactstst = WebUI.getAttribute(findTestObject('Object Repository/CMS/Clients/ActivationStatus/input_activation_status'), 'value')

if (checkactstst != actstat) {
	throw new StepErrorException('Статус активации не сохранился на третьей итерации!')
}

WebUI.click(findTestObject('Object Repository/CMS/Clients/ActivationStatus/div_list_activation_status'))

actstat2 = WebUI.getText(findTestObject('Object Repository/CMS/Clients/ActivationStatus/li_secont_activation_status'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ActivationStatus/li_secont_activation_status'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ActivationStatus/span_back'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ActivationStatus/span_accept_no_save'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ClientList/span_activation_status'))

checkactstst = WebUI.getAttribute(findTestObject('Object Repository/CMS/Clients/ActivationStatus/input_activation_status'), 'value')

if (checkactstst != actstat) {
	throw new StepErrorException('Статус активации без сохранения изменился!')
}







