/**
 * @description Форма обратного звонка на главной странице
 * @step Нажатие на кнопку "Обратный звонок"
 * @step Ввевод телефона
 * @step Ввод  значения в поле "Контактное лицо"
 * @step Отправка данных
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
import urlSite.BaseLink as BaseLink

WebUI.click(findTestObject('Site/TestCallBack/a_call_back'))

urlThis = WebUI.getUrl()

if (urlThis == BaseLink.getUrlDef()) {
    WebUI.verifyElementVisible(findTestObject('Site/TestCallBack/div_js_callback_form'))
}

WebUI.setText(findTestObject('Site/TestCallBack/input_phone'), '88888888')

WebUI.setText(findTestObject('Site/TestCallBack/input_person'), 'Test')

WebUI.click(findTestObject('Site/TestCallBack/label_form_checkbox_trigger'))

WebUI.click(findTestObject('Site/TestCallBack/button_send'))

not_run: err = findTestObject('Site/TestCallBack/div_form_errors')

/*if (err) {
	throw new com.kms.katalon.core.exception.StepFailedException('Show error form')
}*/

