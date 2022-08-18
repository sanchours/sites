/**
 * @description Проверка порядка вывода позиций 
 * @step Заходим в раздел Баннеры
 * @step Переходим в первый баннер с таблице
 * @step Заполняем текст баннера, ставим вывод в левом столбце, галочки Активность и На главной, в поле порядок ставим 1, сохраняем изменения
 * @step Переходим во второй баннер с таблице
 * @step Заполняем текст баннера, ставим вывод в левом столбце, галочки Активность и На главной, в поле порядок ставим 2, сохраняем изменения
 * @step Переходим на главную
 * @step Смотрим последовательность баннеров
 * @step Переходим в админку и заходим в раздел Баннеры
 * @step Меняем позицию у первого баннера на 3
 * @step Переходим на главную
 * @step Смотрим последовательность баннеров
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
import data.BannersData as BannersData
import org.openqa.selenium.WebDriver as WebDriver
import org.openqa.selenium.WebElement as WebElement
import com.kms.katalon.core.webui.driver.DriverFactory as DriverFactory
import org.junit.After as After
import org.openqa.selenium.By as By
import org.openqa.selenium.By.ByClassName as ByClassName
import org.openqa.selenium.By.ByXPath as ByXPath
import com.kms.katalon.core.testobject.ConditionType
import com.kms.katalon.core.exception.StepErrorException as StepErrorException

BannersData DataBanners = new BannersData()

WebUI.click(findTestObject('Object Repository/CMS/News/CreateNewNews/div_top_menu'))

WebUI.click(findTestObject('Object Repository/CMS/News/CheckNewsOnMain/div_main'))

WebUI.click(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/div_banner_editor'))

WebUI.doubleClick(findTestObject('Object Repository/CMS/Banners/CheckDelBanners/div_name_first_banner'))

WebUI.setText(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/body_text_banner'), 'banner text 1')

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
	
WebUI.setText(findTestObject('Object Repository/CMS/Banners/CheckPositionBanner/input_sort_banner'), '1')
		
WebUI.click(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/span_save_banner'))
	
WebUI.doubleClick(findTestObject('Object Repository/CMS/Banners/CheckPositionBanner/div_name_second_banner'))

WebUI.setText(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/body_text_banner'), 'banner text 2')

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
	
WebUI.setText(findTestObject('Object Repository/CMS/Banners/CheckPositionBanner/input_sort_banner'), '2')
		
WebUI.click(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/span_save_banner'))

WebUI.navigateToUrl(BaseLink.getUrlDef())

Textfirstban = WebUI.getText(findTestObject('Object Repository/CMS/Banners/CheckTextBanner/div_first_left_banner'))

if (Textfirstban != 'banner text 1') {
	
	throw new StepErrorException("banner content is not as expected")
	
}

Textsectban = WebUI.getText(findTestObject('Object Repository/CMS/Banners/CheckPositionBanner/div_second_left_banner'))

if (Textsectban != 'banner text 2') {
	
	throw new StepErrorException("banner content is not as expected")
	
}
WebDriver driver = DriverFactory.getWebDriver()
numban = driver.findElements(By.xpath("//div[contains(@class,'column__left-indent')]/div[contains(@class,'b-bannerleft')]")).size()

WebUI.navigateToUrl(BaseLink.getUrlDefAdm())

WebUI.click(findTestObject('Object Repository/CMS/News/CreateNewNews/div_top_menu'))

WebUI.click(findTestObject('Object Repository/CMS/News/CheckNewsOnMain/div_main'))

WebUI.click(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/div_banner_editor'))

WebUI.doubleClick(findTestObject('Object Repository/CMS/Banners/CheckDelBanners/div_name_first_banner'))

WebUI.setText(findTestObject('Object Repository/CMS/Banners/CheckPositionBanner/input_sort_banner'), '3')

WebUI.click(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/span_save_banner'))

WebUI.navigateToUrl(BaseLink.getUrlDef())

Textfirstbanv2 = WebUI.getText(findTestObject('Object Repository/CMS/Banners/CheckTextBanner/div_first_left_banner'))

if (Textfirstbanv2 != 'banner text 2') {
	
	throw new StepErrorException("banners are not swapped")
	
}

Textsectbanv2 = WebUI.getText(findTestObject('Object Repository/CMS/Banners/CheckPositionBanner/div_second_left_banner'))

if (Textsectbanv2 != 'banner text 1') {
	
	throw new StepErrorException("banners are not swapped")
	
}
