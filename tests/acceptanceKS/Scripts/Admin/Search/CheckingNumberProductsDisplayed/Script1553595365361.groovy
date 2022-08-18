/**
 * @description Проверка количества выводимых позиций поиска
 * @step Открываем новую вкладку
 * @step Вводим в мини форму поиска(левое меню) Nazvanie Tovara
 * @step Нажимаем кнопку поиска
 * @step Проверяем результат поиска на наличие второго элемента
 * @step Переходим на первую вкладку
 * @step В настройках поиска в поле количество выводимых позиций удаляем ноль, оставляя 1
 * @step Сохраняем. Переходим на вторую вкладку
 * @step Жмем кнопку поиска
 * @step Проверяем результат поиска на наличие первого элемента, пагинатора и отсутсвие второго элемента результата поиска
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
import urlSite.BaseLink as BaseLink
import org.openqa.selenium.JavascriptExecutor as JavascriptExecutor
import org.openqa.selenium.WebDriver as WebDriver
import com.kms.katalon.core.webui.driver.DriverFactory as DriverFactory
import data.CatalogData as CatalogData
import data.SearchData as SearchData
import org.openqa.selenium.Keys as Keys

CatalogData DataCatalog = new CatalogData()
SearchData DataSearch = new SearchData()

WebUI.click(findTestObject('CMS/Navigate/span_control_panel'))

WebUI.click(findTestObject('Object Repository/CMS/Search/AdminPage/div_search'))

WebUI.click(findTestObject('Object Repository/CMS/Search/AdminPage/div_search_type_arrow'))

WebUI.click(findTestObject('Object Repository/CMS/Search/AdminPage/li_first_item_list_search_settings'))

WebUI.click(findTestObject('Object Repository/CMS/Search/AdminPage/span_save_search'))

WebUI.delay(1)

WebDriver driver = DriverFactory.getWebDriver()

JavascriptExecutor js = ((driver) as JavascriptExecutor)

js.executeScript("window.open();")

WebUI.switchToWindowIndex(1)

WebUI.navigateToUrl(BaseLink.getUrlDef())

WebUI.setText(findTestObject('Object Repository/CMS/Search/Main/input_search_main'), DataCatalog.itemname)

WebUI.click(findTestObject('Object Repository/CMS/Search/Main/button_mini_form_search'))

WebUI.verifyElementPresent(findTestObject('Object Repository/CMS/Search/Result/div_second_result_search'), 2)

WebUI.switchToWindowIndex(0)

WebUI.delay(1)

WebUI.sendKeys(findTestObject('Object Repository/CMS/Search/AdminPage/input_number_search_positions'), Keys.chord(Keys.BACK_SPACE))
//WebUI.sendKeys(findTestObject('Object Repository/CMS/Search/AdminPage/input_number_search_positions'), DataSearch.numitems)
//WebUI.sendKeys(findTestObject('Object Repository/CMS/Search/AdminPage/input_number_search_positions'), Keys.chord(Keys.CONTROL, 'a')) //выделяет все содержимое в поле

WebUI.click(findTestObject('Object Repository/CMS/Search/AdminPage/span_save_search'))

WebUI.delay(1)

WebUI.switchToWindowIndex(1)

WebUI.click(findTestObject('Object Repository/CMS/Search/SearchPage/button_search_page'))

WebUI.verifyElementPresent(findTestObject('Object Repository/CMS/Search/Result/div_first_result_search'), 3)

WebUI.verifyElementNotPresent(findTestObject('Object Repository/CMS/Search/Result/div_second_result_search'), 1)

WebUI.verifyElementPresent(findTestObject('Object Repository/CMS/Search/Result/div_pageline_search'), 1)



WebUI.switchToWindowIndex(0)

WebUI.click(findTestObject('Object Repository/CMS/Search/AdminPage/div_arrow_down')) //Приводим кол-во к 0

WebUI.click(findTestObject('Object Repository/CMS/Search/AdminPage/span_save_search'))

WebUI.switchToWindowIndex(1)

WebUI.click(findTestObject('Object Repository/CMS/Search/SearchPage/button_search_page'))

WebUI.verifyElementNotPresent(findTestObject('Object Repository/CMS/Search/Result/div_first_result_search'), 2)