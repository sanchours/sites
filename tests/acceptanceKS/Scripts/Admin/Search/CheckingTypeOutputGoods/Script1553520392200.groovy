/**
 * @description Проверка тип вывода товаров
  * @step Заходим в настройки поиска в админке
  * @step Выбираем в поле "Тип поиска" третий элемент
  * @step Выбираем в поле "Тип вывода товаров" первый элемент. Сохраняем настройки
  * @step Проверяем поле "Тип вывода товаров" на значение "список"
  * @step Открываем новую вкладку с главной страницей
  * @step Производим поиска Nazvanie Tovara
  * @step Проверяем что класс результата поиска соответствует заданному нами типу
  * @step Переходим на первую вкладку
  * @step Выбираем в поле "Тип вывода товаров" второй элемент. Сохраняем настройки
  * @step Проверяем поле "Тип вывода товаров" на значение "галерея"
  * @step Переходим на вторую вкладку. Жмем кнопку поиска
  * @step Проверяем что класс результата поиска соответствует заданному нами типу
  * @step Переходим на первую вкладку
  * @step Выбираем в поле "Тип вывода товаров" третий элемент. Сохраняем настройки
  * @step Проверяем поле "Тип вывода товаров" на значение "таблица"
  * @step Переходим на вторую вкладку. Жмем кнопку поиска
  * @step Проверяем что класс результата поиска соответствует заданному нами типу
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
import com.kms.katalon.core.exception.StepErrorException as StepErrorException
import data.CatalogData as CatalogData


CatalogData DataCatalog = new CatalogData()

WebUI.click(findTestObject('CMS/Navigate/span_control_panel'))

WebUI.click(findTestObject('Object Repository/CMS/Search/AdminPage/div_search'))

WebUI.click(findTestObject('Object Repository/CMS/Search/AdminPage/div_search_type_arrow'))

WebUI.click(findTestObject('Object Repository/CMS/Search/AdminPage/li_third_item_list_search_settings'))

WebUI.click(findTestObject('Object Repository/CMS/Search/AdminPage/div_output_type_arrow'))

WebUI.click(findTestObject('Object Repository/CMS/Search/AdminPage/li_first_item_second_list'))

WebUI.click(findTestObject('Object Repository/CMS/Search/AdminPage/span_save_search'))

if (WebUI.getAttribute(findTestObject('Object Repository/CMS/Search/AdminPage/input_type_output_goods'), 'value') != 'список'){
	throw new StepErrorException('Тип вывода товара "список" не сохранился')
}

WebUI.delay(1)

WebDriver driver = DriverFactory.getWebDriver()

JavascriptExecutor js = ((driver) as JavascriptExecutor)

js.executeScript("window.open();")

WebUI.switchToWindowIndex(1)

WebUI.navigateToUrl(BaseLink.getUrlDef())

WebUI.setText(findTestObject('Object Repository/CMS/Search/Main/input_search_main'), DataCatalog.itemname)

WebUI.click(findTestObject('Object Repository/CMS/Search/Main/button_mini_form_search'))

WebUI.delay(1)

if (WebUI.getAttribute(findTestObject('Object Repository/CMS/Search/Result/div_result_catalogbox'), 'class') != 'b-catalogbox b-catalogbox-list js_goods_container'){
	throw new StepErrorException('Тип вывода товара не "Список"')
}

WebUI.switchToWindowIndex(0)

WebUI.click(findTestObject('Object Repository/CMS/Search/AdminPage/div_output_type_arrow'))

WebUI.click(findTestObject('Object Repository/CMS/Search/AdminPage/li_second_item_list_search_settings'))

WebUI.click(findTestObject('Object Repository/CMS/Search/AdminPage/span_save_search'))

WebUI.delay(1)

if (WebUI.getAttribute(findTestObject('Object Repository/CMS/Search/AdminPage/input_type_output_goods'), 'value') != 'галерея'){
	throw new StepErrorException('Тип вывода товара "галерея" не сохранился')
}

WebUI.switchToWindowIndex(1)

WebUI.click(findTestObject('Object Repository/CMS/Search/SearchPage/button_search_page'))

WebUI.delay(1)

if (WebUI.getAttribute(findTestObject('Object Repository/CMS/Search/Result/div_result_catalogbox'), 'class') != 'b-catalogbox b-catalogbox-gal js_goods_container'){
	throw new StepErrorException('Тип вывода товара не "Галерея"')
}

WebUI.switchToWindowIndex(0)

WebUI.click(findTestObject('Object Repository/CMS/Search/AdminPage/div_output_type_arrow'))

WebUI.click(findTestObject('Object Repository/CMS/Search/AdminPage/li_third_item_list_search_settings'))

WebUI.click(findTestObject('Object Repository/CMS/Search/AdminPage/span_save_search'))

WebUI.delay(1)

if (WebUI.getAttribute(findTestObject('Object Repository/CMS/Search/AdminPage/input_type_output_goods'), 'value') != 'таблица'){
	throw new StepErrorException('Тип вывода товара "таблица" не сохранился')
}

WebUI.switchToWindowIndex(1)

WebUI.click(findTestObject('Object Repository/CMS/Search/SearchPage/button_search_page'))

WebUI.delay(1)

if (WebUI.getAttribute(findTestObject('Object Repository/CMS/Search/Result/div_result_catalogbox'), 'class') != 'b-catalogbox b-catalogbox-table js_goods_container'){
	throw new StepErrorException('Тип вывода товара не "таблица"')
}










