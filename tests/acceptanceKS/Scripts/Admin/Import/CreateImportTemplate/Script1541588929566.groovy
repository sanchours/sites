/**
 * @description Создание шаблона
 * @step Пререход в интерфейс создания шаблона
 * @step Установка названия
 * @step Выбор типа загрузки файла
 * @step Выбор файла
 * @step Выбор кодировки
 * @step Сохранение
 *
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

WebUI.doubleClick(findTestObject('CMS/Import/InstallImportModule/div_import_in_control_panel'))

WebUI.click(findTestObject('CMS/Import/CreateTemplateImport/button_add_new_template_import'))

WebUI.setText(findTestObject('CMS/Import/CreateTemplateImport/input_name_template'), 'Test')

WebUI.click(findTestObject('CMS/Import/CreateTemplateImport/input_card_list'))

WebUI.click(findTestObject('CMS/Import/CreateTemplateImport/input_card_list_item'))

WebUI.click(findTestObject('CMS/Import/CreateTemplateImport/input_type_provider_list'))

WebUI.click(findTestObject('CMS/Import/CreateTemplateImport/intput_type_provider_list_item'))

WebUI.click(findTestObject('CMS/Import/CreateTemplateImport/input_type_list'))

WebUI.click(findTestObject('CMS/Import/CreateTemplateImport/input_type_list_item'))

WebUI.click(findTestObject('CMS/Import/CreateTemplateImport/input_a_source'))

WebUI.setText(findTestObject('CMS/Import/CreateTemplateImport/input_a_source'), '/tests/acceptanceKS/Files/dlya_testa.csv')

WebUI.click(findTestObject('CMS/Import/CreateTemplateImport/div_encoding_list'))

WebUI.click(findTestObject('CMS/Import/CreateTemplateImport/div_encoding_item'))

//WebUI.setText(findTestObject('CMS/Import/CreateTemplateImport/input_a_source'), '/test')
WebUI.click(findTestObject('CMS/Import/CreateTemplateImport/button_save'))

WebUI.verifyElementPresent(findTestObject('CMS/Import/CreateTemplateImport/div_check_object'), 0)

