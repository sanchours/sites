/**
 * @description Проверка удаления баннерар
 * @step Заходим в раздел Баннеры
 * @step Берем текст названия баннера у первого баннера
 * @step Удаляем первый баннер в таблице
 * @step Проверяем название первого баннера
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
import com.thoughtworks.selenium.webdriven.commands.GetText
import internal.GlobalVariable as GlobalVariable
import com.kms.katalon.core.webui.driver.DriverFactory as DriverFactory
import org.openqa.selenium.WebElement as WebElement
import org.openqa.selenium.WebDriver as WebDriver
import org.openqa.selenium.By as By
import com.kms.katalon.core.exception.StepErrorException as StepErrorException

WebUI.click(findTestObject('Object Repository/CMS/News/CreateNewNews/div_top_menu'))

WebUI.click(findTestObject('Object Repository/CMS/News/CheckNewsOnMain/div_main'))

WebUI.click(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/div_banner_editor'))

WebDriver driver = DriverFactory.getWebDriver()

titlefirst = WebUI.getText(findTestObject('Object Repository/CMS/Banners/CheckDelBanners/div_name_first_banner'))

WebUI.click(findTestObject('Object Repository/CMS/Banners/CheckDelBanners/img_del_first_banner'))

WebUI.click(findTestObject('Object Repository/CMS/Banners/CheckDelBanners/span_confirm_del_banner'))

WebUI.refresh()

titlesec = WebUI.getText(findTestObject('Object Repository/CMS/Banners/CheckDelBanners/div_name_first_banner'))

if (titlefirst == titlesec) {
	
	throw new StepErrorException("Error occurred while deleting")
	
}