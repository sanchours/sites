/**
 * @description Создание баннера
 * @step Переход на панель управления
 * @step Преход на вкладку "Слайдер"
 * @step Нажатие на кнопку "Добавить баннер"
 * @step Выбор раздела для показа
 * @step Установка галочки "Показывать на внутренних страницах"
 * @step Выбор типа навигации для слайдера
 * @step Нажатие на кнопку сохранить
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

WebUI.click(findTestObject('CMS/Banner/span_control_panel'))

WebUI.click(findTestObject('CMS/Banner/div_slider'))

WebUI.click(findTestObject('CMS/Banner/button_create_new_banner'))

WebUI.setText(findTestObject('CMS/Banner/input_title'), 'NewBanner')

WebUI.click(findTestObject('CMS/Banner/input_section'))

WebUI.click(findTestObject('CMS/Banner/li_section'))

WebUI.click(findTestObject('CMS/Banner/input_navigation'))

WebUI.click(findTestObject('CMS/Banner/li_navigation_preview'))

WebUI.click(findTestObject('CMS/Banner/button_save'))

