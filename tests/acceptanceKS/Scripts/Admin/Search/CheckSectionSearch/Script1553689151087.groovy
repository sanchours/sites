/**
 * @description Проверка типа поиска "Общий"
 * @step Переход в Панель управления
 * @step Переход в раздел "Поиск"
 * @step Выбираем из выпадающего списка "Тип поиска" первый элемент
 * @step Нажатие на кнопку "Сохранить"
 * @step Открываем новую вкладку на главной странице
 * @step Вводим Nazvanie Tovara в поле мини формы поиска
 * @step Проверяем наличие второго результата и отсутствие третьего
 * @step Открываем список разделов для поиска и ищем созданный нами раздел. Кликаем по нему
 * @step Кликаем по кнопке поиск
 * @step Проверяем ссылку первого элемента результата поиска
 * @step Проверяем отсутствие второго элемента результата поиска
 *  */
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
import org.openqa.selenium.JavascriptExecutor as JavascriptExecutor
import org.openqa.selenium.WebDriver as WebDriver
import com.kms.katalon.core.webui.driver.DriverFactory as DriverFactory
import urlSite.BaseLink as BaseLink
import data.CatalogData as CatalogData
import data.SearchData as SearchData
import org.openqa.selenium.WebDriver as WebDriver
import org.openqa.selenium.WebElement as WebElement
import org.junit.After as After
import org.openqa.selenium.By as By
import com.kms.katalon.core.exception.StepErrorException as StepErrorException
import org.openqa.selenium.By.ByClassName as ByClassName
import org.openqa.selenium.By.ByXPath as ByXPath


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

WebUI.verifyElementPresent(findTestObject('Object Repository/CMS/Search/Result/div_second_result_search'), 3)

WebUI.verifyElementNotPresent(findTestObject('Object Repository/CMS/Search/Result/div_third_result_search'), 1)

WebUI.click(findTestObject('Object Repository/CMS/Search/SearchPage/span_search_section_arrow_site'))

WebElement containerSection = driver.findElement(By.xpath('//span[contains(@class,\'select2-dropdown select2-dropdown--below\')]//ul[contains(@class,\'select2-results__options\')]')) //выбор места в котором будем считать

ArrayList<WebElement> Section = new ArrayList<WebElement>()

Section.addAll(containerSection.findElements(By.xpath('//span[contains(@class,\'select2-dropdown select2-dropdown--below\')]//ul[contains(@class,\'select2-results__options\')]/li'))) //элементы которые мы считаем

int countSectins = Section.size()

println(countSectins)

for (int numberField = 1; numberField <= countSectins; numberField++) {
	nameSection = driver.findElement(By.xpath(('//span[contains(@class,\'select2-dropdown select2-dropdown--below\')]//ul[contains(@class,\'select2-results__options\')]/li[' + numberField) + ']')).getText()
	if (nameSection == '-' + DataCatalog.titlesection){
		driver.findElement(By.xpath(('//span[contains(@class,\'select2-dropdown select2-dropdown--below\')]//ul[contains(@class,\'select2-results__options\')]/li[' + numberField) + ']')).click()
		numberField = countSectins + 1
				
	}
}

WebUI.click(findTestObject('Object Repository/CMS/Search/SearchPage/button_search_page'))


hrefFirst = WebUI.getAttribute(findTestObject('Object Repository/CMS/Search/Result/a_first_result_goods'), 'href')

if (hrefFirst != BaseLink.getUrlDef() + '/' + DataCatalog.aliassection + '/' + DataCatalog.tecnicalname + '/' ) {
	throw new StepErrorException('Не та ссылка')
}

WebUI.verifyElementNotPresent(findTestObject('Object Repository/CMS/Search/Result/div_second_result_search'), 1)