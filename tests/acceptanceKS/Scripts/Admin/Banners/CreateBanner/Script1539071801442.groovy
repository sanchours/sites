/**
 * @description Проверка создания баннера
 * @step Переходим на главную страницу и смотрим количество баннеров в левой колонке
 * @step Переходим в админку и заходим в раздел Баннеры
 * @step Жмем добавить баннер
 * @step Заполняем у него название баннера, ставим вывод в левом столбце, ставим галочки Активность и На главной
 * @step Жмем сохранить
 * @step Переходим на главную страницу и проверяем количество баннеров в левой колонке
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
import com.kms.katalon.core.webui.driver.DriverFactory as DriverFactory
import org.openqa.selenium.WebElement as WebElement
import org.openqa.selenium.WebDriver as WebDriver
import org.openqa.selenium.By as By
import com.kms.katalon.core.exception.StepErrorException as StepErrorException

WebUI.navigateToUrl(BaseLink.getUrlDef())

WebDriver driver = DriverFactory.getWebDriver()

numban = driver.findElements(By.xpath("//div[contains(@class,'column__left-indent')]/div[contains(@class,'b-bannerleft')]")).size()

WebUI.navigateToUrl(BaseLink.getUrlDefAdm())

WebUI.click(findTestObject('Object Repository/CMS/News/CreateNewNews/div_top_menu'))

WebUI.click(findTestObject('Object Repository/CMS/News/CheckNewsOnMain/div_main'))

WebUI.click(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/div_banner_editor'))

WebUI.click(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/span_add_banner'))

WebUI.setText(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/input__title_banner'), "title")

WebUI.click(findTestObject('CMS/Banners/CreateNewBanner/div_position_banner'))

WebUI.click(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/li_left_column_banner'))

if (WebUI.verifyElementChecked(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/input_activ_banner'), 1, FailureHandling.OPTIONAL) ==
	true) {
		WebUI.click(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/input_activ_banner'))
		}
	
if (WebUI.verifyElementChecked(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/input_on_main_banner'), 1, FailureHandling.OPTIONAL) ==
	true) {
		WebUI.click(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/input_on_main_banner'))
		}
		

WebUI.click(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/span_save_banner'))

WebUI.navigateToUrl(BaseLink.getUrlDef())

numban2 = driver.findElements(By.xpath("//div[contains(@class,'column__left-indent')]/div[contains(@class,'b-bannerleft')]")).size()

numban++

if (numban != numban2) {
	
	throw new StepErrorException("the number of banners is not as expected")
	
}


