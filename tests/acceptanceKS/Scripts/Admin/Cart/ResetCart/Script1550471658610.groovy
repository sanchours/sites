 /**
 * @description Очистка корзины
 * @step Жмем кнопку очистки корзины
 * @step Подтверждаем удаление
 * @step Проверяем на странице имена товараров
 * @spet Проверяем отсутствие заголовка первого товара на странице
 */ import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
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
import com.kms.katalon.core.exception.StepErrorException as StepErrorException
import internal.GlobalVariable as GlobalVariable
import org.openqa.selenium.Keys as Keys
import data.CatalogData as CatalogData
import urlSite.BaseLink as BaseLink
import com.kms.katalon.core.cucumber.keyword.CucumberBuiltinKeywords as CucumberKW

CatalogData DataCatalog = new CatalogData()

WebUI.click(findTestObject('Object Repository/CMS/Cart/DeleteItem/a_reset_cart'))

WebUI.click(findTestObject('CMS/Cart/DeleteItem/button_accept_del_item'))

WebUI.delay(1)

WebUI.refresh()

WebUI.delay(1)

WebUI.verifyTextNotPresent(DataCatalog.itemname, false)

WebUI.verifyTextNotPresent(DataCatalog.itemnamesec, false)

WebUI.verifyElementNotPresent(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/div_first_title_cart'), 1)
