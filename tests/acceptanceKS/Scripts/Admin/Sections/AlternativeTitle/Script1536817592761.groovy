 /**
 * @description Альтернативный заголовок
 * @step Установка значения в поле "Альтернативный заголовок"
 * @step Сохранение
 * @step Переход на страницу
 * @step Проверка альтернативного заголовка
 */ import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
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

alternativeTitle = 'Alternative Title'

WebUI.setText(findTestObject('CMS/Sections/AlternativeTitle/input_alternative_title'), alternativeTitle)

WebUI.click(findTestObject('CMS/Sections/AlternativeTitle/button_save'))

WebUI.delay(3)

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_catalog_link'))

WebUI.switchToWindowIndex(1)

WebUI.verifyElementText(findTestObject('CMS/Sections/AlternativeTitle/h1_alternative_title_on_page'), alternativeTitle)

