/**
 * @description Добавление товара
 * @step Переход во вкладку "Каталог"
 * @step Нажатие на кнопку "Добавить"
 * @step Заполнение поля "Название товара"
 * @step Заполнение поля "Артикул"
 * @step Заполнение поля "Цена"
 * @step Сохранение товара
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
import data.CatalogData as CatalogData

CatalogData DataCatalog = new CatalogData()

WebUI.click(findTestObject('Object Repository/CMS/Cart/CreateCatalogSection/span_tab_catalog'))

WebUI.click(findTestObject('Object Repository/CMS/Cart/CreateCatalogSection/div_create_new_good'))

WebUI.setText(findTestObject('CMS/Goods/input_title'), DataCatalog.itemname)

WebUI.setText(findTestObject('CMS/Goods/input_article'), DataCatalog.tecnicalname)

WebUI.setText(findTestObject('CMS/Goods/input_price'), DataCatalog.price)

WebUI.setText(findTestObject('CMS/Goods/input_alias'), DataCatalog.tecnicalname)

WebUI.click(findTestObject('CMS/Goods/button_save'))

WebUI.click(findTestObject('Object Repository/CMS/Cart/CreateCatalogSection/span_editor_catalog_tab'))