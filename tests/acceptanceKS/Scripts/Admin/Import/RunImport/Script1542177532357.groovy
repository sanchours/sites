/**
 * @description Запуск импорта
 * @step Пререход в интерфейс связи полей 
 * @step Установка связей 
 * @step Сохранение 
 * @step Запуск импорта
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

WebUI.click(findTestObject('CMS/Import/RunImport/button_provider_settings'))

WebUI.delay(3)

WebUI.click(findTestObject('CMS/Import/RunImport/button_matching_field'))

WebUI.click(findTestObject('CMS/Import/RunImport/section_field_unloading_list'))

WebUI.click(findTestObject('CMS/Import/RunImport/section_field_unloading_item'))

WebUI.click(findTestObject('CMS/Import/RunImport/section_field_unloading_list'))

WebUI.click(findTestObject('CMS/Import/RunImport/section_type_list'))

WebUI.click(findTestObject('CMS/Import/RunImport/section_type_item'))

WebUI.click(findTestObject('CMS/Import/RunImport/title_field_unloading_list'))

WebUI.click(findTestObject('CMS/Import/RunImport/title_field_unloading_item'))

WebUI.click(findTestObject('CMS/Import/RunImport/title_field_unloading_list'))

WebUI.click(findTestObject('CMS/Import/RunImport/title_type_list'))

WebUI.click(findTestObject('CMS/Import/RunImport/title_type_item'))

WebUI.click(findTestObject('CMS/Import/RunImport/article_field_unloading_list'))

WebUI.click(findTestObject('CMS/Import/RunImport/article_field_unloading_item'))

WebUI.click(findTestObject('CMS/Import/RunImport/article_field_unloading_list'))

WebUI.click(findTestObject('CMS/Import/RunImport/article_type_list'))

WebUI.click(findTestObject('CMS/Import/RunImport/article_type_item'))

WebUI.click(findTestObject('CMS/Import/RunImport/button_save_field'))

WebUI.click(findTestObject('CMS/Import/RunImport/button_settings_field'))

WebUI.click(findTestObject('CMS/Import/RunImport/button_save_settings'))

WebUI.click(findTestObject('CMS/Import/RunImport/button_run_import'))

