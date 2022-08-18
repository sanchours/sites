/**
 * @description Редактирование справочника
 * @step Измениение значения полей элемента справочника
 * @step Сохранение
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
import data.GoodsData as GoodsData

GoodsData DataGoods = new GoodsData()

DataGoods.setChangeData()

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/button_item_edit_of directory'))

WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_name_item'), DataGoods.itemname)

WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_technical_name_item'), DataGoods.tecnicalname)

WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_time'), DataGoods.time)

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/input_checkbox'), FailureHandling.STOP_ON_FAILURE)

WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_money'), DataGoods.money)

WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_fractional_numbers'), DataGoods.factonalnumbers)

WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_string'), DataGoods.string)

WebUI.setText(findTestObject('CMS/Goods/CreateNewDirectory/input_textarea'), DataGoods.textarea)

WebUI.click(findTestObject('CMS/Goods/CreateNewDirectory/button_save_item_in_directory'))

