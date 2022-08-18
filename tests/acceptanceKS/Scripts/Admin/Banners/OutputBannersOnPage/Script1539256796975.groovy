/**
 * @description Проверка вывода баннера на странице
 * @step Считаем количество баннеров в созданном разделе и на главной
 * @step Переходим в админскую часть
 * @step Заходим в раздел Баннеры
 * @step Жмем на создание баннера
 * @step Выставляем в настройках что бы он выводился только в созданном разделе
 * @step Проверяем количество баннеров в созданном разделе, если не изменилось выводим ошибку
 * @step Проверяем количество баннеров на главной, если не изменилось выводим ошибку
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

WebUI.navigateToUrl(BaseLink.getUrlDef() + '/' + DataBanners.sectionalias)

WebDriver driver = DriverFactory.getWebDriver()

size1 = driver.findElements(By.xpath("//div[contains(@class,'column__left-indent')]/div[contains(@class,'b-bannerleft')]")).size()

WebUI.navigateToUrl(BaseLink.getUrlDef())

size2 = driver.findElements(By.xpath("//div[contains(@class,'column__left-indent')]/div[contains(@class,'b-bannerleft')]")).size()

WebUI.navigateToUrl(BaseLink.getUrlDefAdm())

WebUI.click(findTestObject('Object Repository/CMS/News/CreateNewNews/div_top_menu'))

WebUI.click(findTestObject('Object Repository/CMS/News/CheckNewsOnMain/div_main'))

WebUI.click(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/div_banner_editor'))

WebUI.click(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/span_add_banner'))

if (WebUI.verifyElementChecked(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/input_activ_banner'), 1, FailureHandling.OPTIONAL) ==
	true) {
		WebUI.click(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/input_activ_banner'))
		}
if (WebUI.verifyElementChecked(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/input_on_main_banner'), 1, FailureHandling.OPTIONAL) ==
	false) {
		WebUI.click(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/input_on_main_banner'))
		}
if (WebUI.verifyElementChecked(findTestObject('Object Repository/CMS/Banners/OutputBannersOnPage/input_banners_on_all'), 1, FailureHandling.OPTIONAL) ==
	false) {
		WebUI.click(findTestObject('Object Repository/CMS/Banners/OutputBannersOnPage/input_banners_on_all'))
		}
if (WebUI.verifyElementChecked(findTestObject('Object Repository/CMS/Banners/OutputBannersOnPage/input_inside_page'), 1, FailureHandling.OPTIONAL) ==
	false) {
		WebUI.click(findTestObject('Object Repository/CMS/Banners/OutputBannersOnPage/input_inside_page'))
		}
	
int k

namesection = '-' + DataBanners.sectionalias

TestObject to = new TestObject()

WebUI.click(findTestObject('Object Repository/CMS/Banners/OutputBannersOnPage/div_section_to_display'))

for (int numberField = 1; k < 1; numberField++) {

	nameFiled = driver.findElement(By.xpath('//div[contains(@class,\'x-boundlist x-boundlist-floating x-boundlist-default x-layer\')]//li[' + numberField + ']')).getText() 

	if (nameFiled == namesection) {
	k++
	to.addProperty("xpath", ConditionType.EQUALS, '//div[contains(@class,\'x-boundlist x-boundlist-floating x-boundlist-default x-layer\')]//li[' + numberField + ']')
}
}

WebUI.click(to)

WebUI.click(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/span_save_banner'))

WebUI.navigateToUrl(BaseLink.getUrlDef() + '/' + DataBanners.sectionalias)

if (driver.findElements(By.xpath("//div[contains(@class,'column__left-indent')]/div[contains(@class,'b-bannerleft')]")).size() != (size1 +1)) {
	throw new StepErrorException("the number of banners is not as expected")
}

WebUI.navigateToUrl(BaseLink.getUrlDef())

if (driver.findElements(By.xpath("//div[contains(@class,'column__left-indent')]/div[contains(@class,'b-bannerleft')]")).size() != size2) {
	throw new StepErrorException("the number of banners is not as expected")
}


	