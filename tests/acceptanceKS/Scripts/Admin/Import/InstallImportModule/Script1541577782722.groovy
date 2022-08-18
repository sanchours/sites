/**
 * @description Установка модуля "Импорт"
 * @step Пререход в интерфейс "Модули"
 * @step Установка модуля
 * @step Проверка что модуль установлен
 */
import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import com.kms.katalon.core.checkpoint.Checkpoint as Checkpoint
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as Mobile
import com.kms.katalon.core.model.FailureHandling as FailureHandling
import com.kms.katalon.core.testcase.TestCase as TestCase
import com.kms.katalon.core.testdata.TestData as TestData
import com.kms.katalon.core.testobject.TestObject as TestObject
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WS
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import internal.GlobalVariable as GlobalVariable

WebUI.click(findTestObject('CMS/Form/HiddenField/div_control_panel'))

WebUI.click(findTestObject('CMS/Import/InstallImportModule/div_modules'))

WebUI.click(findTestObject('CMS/Import/InstallImportModule/input_module_name'))

WebUI.click(findTestObject('CMS/Import/InstallImportModule/span_control_panel_layer'))

WebUI.doubleClick(findTestObject('CMS/Import/InstallImportModule/div_module_import'))

WebUI.click(findTestObject('CMS/Import/InstallImportModule/button_install'))

WebUI.click(findTestObject('CMS/Import/InstallImportModule/span_ok'))

WebUI.refresh()

WebUI.delay(7)

WebUI.verifyElementPresent(findTestObject('CMS/Import/InstallImportModule/div_import_in_control_panel'), 0)

