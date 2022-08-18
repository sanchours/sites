 /**
 * @description Удаление первого товара в корзине
 * @step Жмем кнопку удалить у первого товара
 * @step Подтвверждаем удаление
 * @step Проверяем название первого товара
 * @step Проверяем на странице имя изначально первого товара
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

WebUI.click(findTestObject('CMS/Cart/DeleteItem/a_del_first_item_in_cart'))

WebUI.click(findTestObject('CMS/Cart/DeleteItem/button_accept_del_item'))

WebUI.delay(1)

WebUI.refresh()

WebUI.delay(1)

Title = WebUI.getText(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/div_first_title_cart'))

if (Title != DataCatalog.itemnamesec) {
	throw new StepErrorException('При удалении товары не сдвинулись!')
}

WebUI.verifyTextNotPresent(DataCatalog.itemname, true)
