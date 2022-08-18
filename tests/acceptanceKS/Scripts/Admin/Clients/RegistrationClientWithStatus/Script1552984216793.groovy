/**
 * @description Проверка регистрации клиента
 * @step Октраваем новую вкладку с главной страницей
 * @step Жмем ссылку регистрация
 * @step Заполняем поля и отправляем форму
 * @step Переходим в админскую часть в раздел клиенты и проверяем присутсвие клиента со статусом зависящем от типа статуса активации
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
import data.OrdersData as OrdersData
import urlSite.BaseLink as BaseLink
import org.openqa.selenium.JavascriptExecutor as JavascriptExecutor
import org.openqa.selenium.WebDriver as WebDriver
import com.kms.katalon.core.webui.driver.DriverFactory as DriverFactory

OrdersData DataOrders = new OrdersData()

WebDriver driver = DriverFactory.getWebDriver()

JavascriptExecutor js = ((driver) as JavascriptExecutor)

js.executeScript("window.open();")

//WebUI.sendKeys(null,Keys.chord(Keys.CONTROL,"t"))

WebUI.switchToWindowIndex(1)

WebUI.navigateToUrl(BaseLink.getUrlDef())

WebUI.click(findTestObject('Object Repository/CMS/Clients/RegistrationClient/a_registration_client'))

WebUI.setText(findTestObject('Object Repository/CMS/Clients/RegistrationClient/input_registrarion_login'), DataOrders.email)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/RegistrationClient/input_registrarion_password'), DataOrders.pass)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/RegistrationClient/input_registrarion_wpassword'), DataOrders.pass)

WebUI.click(findTestObject('Object Repository/CMS/Clients/RegistrationClient/input_registration_checkbox'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/RegistrationClient/button_send_registration_form'))

WebUI.switchToWindowIndex(0)

WebUI.click(findTestObject('CMS/Navigate/span_control_panel'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/div_clients'))

WebUI.verifyElementPresent(findTestObject('Object Repository/CMS/Clients/ClientList/div_first_client'), 2)

if (GlobalVariable.reg == 'automat') {
	WebUI.verifyElementPresent(findTestObject('Object Repository/CMS/Clients/ClientList/img_ban_first_client'), 2)
}

if (GlobalVariable.reg == 'email' || GlobalVariable.reg == 'adm') {
	WebUI.verifyElementPresent(findTestObject('Object Repository/CMS/Clients/ClientList/img_confirm_client_first'), 2)
}
