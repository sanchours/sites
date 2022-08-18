/**
 * @description Добавление товара
 * @step Переход во вкладку "Каталог"
 * @step Переход во вкладку "Товары"
 * @step Выбор раздела для создания товара
 * @step Нажатие на кнопку "Добавить"
 * @step Выбор основного раздела для товара
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

WebUI.click(findTestObject('CMS/Goods/span_catalog'))

WebUI.click(findTestObject('Object Repository/CMS/Goods/div_goods'))

WebUI.click(findTestObject('CMS/Goods/button_section'))

WebUI.click(findTestObject('CMS/Goods/span_product_list'))

WebUI.click(findTestObject('CMS/Goods/button_product_edit'))

WebUI.click(findTestObject('CMS/Goods/input_product_list'))

WebUI.click(findTestObject('CMS/Goods/li_product_list'))

WebUI.setText(findTestObject('CMS/Goods/input_title'), 'Name')

WebUI.setText(findTestObject('CMS/Goods/input_article'), '01001')

WebUI.setText(findTestObject('CMS/Goods/input_price'), '100')

WebUI.click(findTestObject('CMS/Goods/button_save'))

