/**
 * @description Название формы
 * @step Переход на детальную формы обратной связи
 * @step Перход в настройки формы
 * @step Установка значение в параметр "Название формы"
 * @step Нажатие на кнопку "Сохранить"
 * @step Переход в списковую часть модуля формы
 * @step Проверка названия формы
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


WebUI.doubleClick(findTestObject('CMS/Form/ShowForrmHeader/div_form_feedback'))

WebUI.click(findTestObject('CMS/Form/ShowForrmHeader/span_form_settings'))

Title = 'FORMNAME'

WebUI.setText(findTestObject('CMS/Form/ShowForrmHeader/input_form_title'), Title)

WebUI.click(findTestObject('CMS/Form/ShowForrmHeader/input_form_header'))

WebUI.click(findTestObject('CMS/Form/ShowForrmHeader/span_save'))

WebUI.click(findTestObject('CMS/Form/FormName/span_back'))

WebUI.verifyElementText(findTestObject('CMS/Form/FormName/div_form_name'), Title)

