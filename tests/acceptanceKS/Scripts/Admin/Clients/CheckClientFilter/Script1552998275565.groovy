/**
 * @description Проверка работы фильтра по статусам клиента
 * @step В фильтре выбираем забаненые и проверяем что список пуст
 * @step Возвращаем фильтр на отображение всех клиентов
 * @step Баним первого клиента
 * @step В фильтре выбираем забаненые и проверяем что в списке один клиент
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

WebUI.click(findTestObject('Object Repository/CMS/Clients/ClientList/span_client_filter'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ClientList/span_li_furth_filter_client'))

WebUI.verifyElementNotPresent(findTestObject('Object Repository/CMS/Clients/ClientList/div_first_client'), 3)

WebUI.click(findTestObject('Object Repository/CMS/Clients/ClientList/span_client_filter'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ClientList/span_li_all_filter_client'))

WebUI.verifyElementPresent(findTestObject('Object Repository/CMS/Clients/ClientList/div_first_client'), 3)

WebUI.click(findTestObject('Object Repository/CMS/Clients/ClientList/img_ban_first_client'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ClientList/span_client_filter'))

WebUI.click(findTestObject('Object Repository/CMS/Clients/ClientList/span_li_furth_filter_client'))

WebUI.verifyElementPresent(findTestObject('Object Repository/CMS/Clients/ClientList/div_first_client'), 3)

WebUI.verifyElementNotPresent(findTestObject('Object Repository/CMS/Clients/ClientList/div_second_client'), 3)

