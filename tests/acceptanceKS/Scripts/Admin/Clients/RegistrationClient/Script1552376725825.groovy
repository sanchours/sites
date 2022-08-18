/**
 * @description Регистрация клиента
 * @step Жмем ссылку регистрация
 * @step Заполняем поля и отправляем форму
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
import org.openqa.selenium.Keys as Keys
import data.OrdersData as OrdersData
import urlSite.BaseLink as BaseLink


OrdersData DataOrders = new OrdersData()

WebUI.click(findTestObject('Object Repository/CMS/Clients/RegistrationClient/a_registration_client'))

WebUI.setText(findTestObject('Object Repository/CMS/Clients/RegistrationClient/input_registrarion_login'), DataOrders.email)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/RegistrationClient/input_registrarion_password'), DataOrders.pass)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/RegistrationClient/input_registrarion_wpassword'), DataOrders.pass)

WebUI.click(findTestObject('Object Repository/CMS/Clients/RegistrationClient/input_registration_checkbox'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/RegistrationClient/button_send_registration_form'))

WebUI.delay(2)

WebUI.verifyElementPresent(findTestObject('Object Repository/CMS/Clients/RegistrationClient/div_form_registration_request'), 2)


