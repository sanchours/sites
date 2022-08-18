/**
 * @description Удаление полей справочника
 * @step Удаление поля
 * @step Подтверждение удаления
 * @step Проверка, что поля удалены
 */

import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import com.kms.katalon.core.checkpoint.Checkpoint as Checkpoint
import com.kms.katalon.core.checkpoint.CheckpointFactory as CheckpointFactory
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as MobileBuiltInKeywords
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as Mobile
import com.kms.katalon.core.model.FailureHandling as FailureHandling
import com.kms.katalon.core.testcase.TestCase as TestCase
import com.kms.katalon.core.testcase.TestCaseFactory as TestCaseFactory
import com.kms.katalon.core.testdata.TestData as TestData
import com.kms.katalon.core.testdata.TestDataFactory as TestDataFactory
import com.kms.katalon.core.testobject.ObjectRepository as ObjectRepository
import com.kms.katalon.core.testobject.TestObject as TestObject
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WSBuiltInKeywords
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WS
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUiBuiltInKeywords
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import internal.GlobalVariable as GlobalVariable
import org.openqa.selenium.WebDriver as WebDriver
import org.openqa.selenium.WebElement as WebElement
import com.kms.katalon.core.webui.driver.DriverFactory as DriverFactory
import org.junit.After as After
import org.openqa.selenium.By as By
import org.openqa.selenium.By.ByClassName as ByClassName
import org.openqa.selenium.By.ByXPath as ByXPath

WebDriver driver = DriverFactory.getWebDriver()

ArrayList<WebElement> listTypeField = driver.findElements(By.xpath('//div[contains(@class,\'sk-tab-list\')]//table/tbody/tr/td/div/img[2]'))

int countListTypeField = listTypeField.size()

println (countListTypeField)

for (int numberField = 1; numberField <= countListTypeField; numberField++) {
	
	WebUI.delay(1)
	
    nameField = driver.findElement(By.xpath('//div[contains(@class,\'sk-tab-list\')]//table/tbody/tr[2]/td[2]')).getText()
	
    driver.findElement(By.xpath('//div[contains(@class,\'sk-tab-list\')]//table/tbody/tr[2]/td[4]/div/img[2]')).click()
	
	WebUI.delay(1)

    WebUI.click(findTestObject('CMS/Form/DeleteForm/span_ok'))
	
	WebUI.delay(1)
}

WebUI.verifyElementNotPresent(findTestObject('CMS/Goods/DeletFieldInCard/div_name_field'), 1)

