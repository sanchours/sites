/**
 * @description Проверка работы поиска в клиентах
 * @step Проверяем что при вводе имени пользователя отображаются клиенты только с этим именем, проверяем что сброс поиска работает
 * @step Проверяем что при вводе почты пользователя отображаются клиенты только с этой почтой, проверяем что сброс поиска работает
 * @step Проверяем что при вводе номера пользователя отображаются клиенты только с этим номером, проверяем что сброс поиска работает
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

OrdersData DataOrders = new OrdersData()

WebUI.verifyElementPresent(findTestObject('Object Repository/CMS/Clients/ClientList/div_second_client'), 3)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/ClientList/input_name_search'), DataOrders.namesec)

WebUI.click(findTestObject('Object Repository/CMS/Clients/ClientList/div_search_name'))

WebUI.verifyElementNotPresent(findTestObject('Object Repository/CMS/Clients/ClientList/div_second_client'), 3)

WebUI.click(findTestObject('Object Repository/CMS/Clients/ClientList/div_clean_search_name'))

WebUI.verifyElementPresent(findTestObject('Object Repository/CMS/Clients/ClientList/div_second_client'), 3)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/ClientList/input_email_search'), DataOrders.email)

WebUI.click(findTestObject('Object Repository/CMS/Clients/ClientList/div_search_email'))

WebUI.verifyElementNotPresent(findTestObject('Object Repository/CMS/Clients/ClientList/div_second_client'), 3)

WebUI.click(findTestObject('Object Repository/CMS/Clients/ClientList/div_clean_search_email'))

WebUI.verifyElementPresent(findTestObject('Object Repository/CMS/Clients/ClientList/div_second_client'), 3)

WebUI.setText(findTestObject('Object Repository/CMS/Clients/ClientList/input_tel_search'), DataOrders.tel)

WebUI.click(findTestObject('Object Repository/CMS/Clients/ClientList/div_search_tel'))

WebUI.verifyElementNotPresent(findTestObject('Object Repository/CMS/Clients/ClientList/div_second_client'), 3)

WebUI.click(findTestObject('Object Repository/CMS/Clients/ClientList/div_clean_search_tel'))

WebUI.verifyElementPresent(findTestObject('Object Repository/CMS/Clients/ClientList/div_second_client'), 3)











