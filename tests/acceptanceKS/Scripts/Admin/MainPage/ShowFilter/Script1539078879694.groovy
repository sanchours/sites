/**
 * @descripion Вывод фильтра на главную
 * @step Переход впараметры вывовда фильтра на главную
 * @step Выбор раздела с фильтром
 * @step Сохранение
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

WebUI.click(findTestObject('CMS/MainPage/FilterOnMain/div_filter_on_main_settings'))

WebUI.click(findTestObject('CMS/MainPage/FilterOnMain/div_section_of_form_list'))

WebUI.click(findTestObject('CMS/MainPage/FilterOnMain/li_section_of_form_item'))

WebUI.click(findTestObject('CMS/MainPage/NameNewsOnPage/button_save'))

WebUI.click(findTestObject('CMS/Goods/FilterOfCatalogOnPage/div_region_editor'))

WebUI.click(findTestObject('CMS/Goods/FilterOfCatalogOnPage/div_filter_for_catalog'))

