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
import org.openqa.selenium.WebDriver as WebDriver
import org.openqa.selenium.WebElement as WebElement
import com.kms.katalon.core.webui.driver.DriverFactory as DriverFactory
import org.junit.After as After
import org.openqa.selenium.By as By
import org.openqa.selenium.By.ByClassName as ByClassName
import org.openqa.selenium.By.ByXPath as ByXPath
import com.kms.katalon.core.testobject.ConditionType
import com.kms.katalon.core.exception.StepErrorException as StepErrorException

WebDriver driver = DriverFactory.getWebDriver()

countGoods = driver.findElements(By.xpath("//div[contains(@class,\'column__center-indent\')]/div[contains(@class,\'b-catalogbox\')][1]/div/div")).size()

countHit = driver.findElements(By.xpath("//div[contains(@class,\'column__center-indent\')]/div[contains(@class,\'b-catalogbox\')][2]/div/div")).size()

countNew = driver.findElements(By.xpath("//div[contains(@class,\'column__center-indent\')]/div[contains(@class,\'b-catalogbox\')][3]/div/div")).size()

if (countGoods != 1) {
	throw new StepErrorException("Не соответствует количество элементов в контейнере") 
}
if (countHit != 1) {
	throw new StepErrorException("Не соответствует количество элементов в контейнере")
}
if (countNew != 1) {
	throw new StepErrorException("Не соответствует количество элементов в контейнере")
}