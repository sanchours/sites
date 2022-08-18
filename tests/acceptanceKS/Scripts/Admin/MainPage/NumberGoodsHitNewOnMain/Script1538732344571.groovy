/**
 * @description Количество товаров, хитов и новых на главной
 * @step Установка количества товаров на главной
 * @step Установка количества хитов на главной
 * @step Установка количества новинок на главной 
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

WebUI.click(findTestObject('CMS/MainPage/NameCatalogHitsNew/div_settings_catalog_new'))

WebUI.setText(findTestObject('CMS/MainPage/NumberGoodsHitNew/input_number_goods_new_on_main'), '1')

WebUI.click(findTestObject('CMS/MainPage/NameCatalogHitsNew/div_settings_catalog_hit'))

WebUI.setText(findTestObject('CMS/MainPage/NumberGoodsHitNew/input_number_goods_hits_on_main'), '1')

WebUI.click(findTestObject('CMS/MainPage/NameCatalogHitsNew/div_settings_catalog'))

WebUI.setText(findTestObject('CMS/MainPage/NumberGoodsHitNew/input_number_goods_on_main'), '1')

WebUI.click(findTestObject('CMS/MainPage/NameNewsOnPage/button_save'))

