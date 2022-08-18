/**
 * @description Проверка плейсхолдера в поле поиска на главной
  * @step Заходим в настройки поиска
  * @step Проверяем отсутствие галочки в поле активности плейсхолдера
  * @step Ставим галочку, если ее нет
  * @step Сохраняем. Открываем новую вкладку
  * @step Кликаем в поле поиска на главной
  * @step Проверяем на наличие плейсхолдера
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

WebUI.click(findTestObject('CMS/Navigate/span_control_panel'))

WebUI.click(findTestObject('Object Repository/CMS/Search/AdminPage/div_search'))

if (WebUI.verifyElementChecked(findTestObject('Object Repository/CMS/Search/AdminPage/input_checkbox_placeholder'), 0, FailureHandling.OPTIONAL) ==
	false) {
		WebUI.click(findTestObject('Object Repository/CMS/Search/AdminPage/input_checkbox_placeholder'))
	}
	
WebUI.click(findTestObject('Object Repository/CMS/Search/AdminPage/span_save_search'))

WebDriver driver = DriverFactory.getWebDriver()

JavascriptExecutor js = ((driver) as JavascriptExecutor)

js.executeScript("window.open();")

WebUI.switchToWindowIndex(1)

WebUI.navigateToUrl(BaseLink.getUrlDef())

WebUI.click(findTestObject('Object Repository/CMS/Search/Main/input_search_main'))

Text = WebUI.getAttribute(findTestObject('Object Repository/CMS/Search/Main/input_search_main'), 'placeholder')

if (Text != '') {
	throw new StepErrorException('Placeholder не исчез!')
}



	
	
	