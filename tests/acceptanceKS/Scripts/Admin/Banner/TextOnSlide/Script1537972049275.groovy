/**
 * @description Текст на слайдах
 * @step Переход в панель управления
 * @step Переход в модуль "Слайдер"
 * @step Переход в интерфейс редактирования сладера
 * @step Переход в интерфейс редактирования слайда
 * @step Добавление текста на слайд
 * @step Переход на страницу сайта
 * @step Проверка наличия текста на слайде
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
import urlSite.BaseLink as BaseLink

WebUI.click(findTestObject('CMS/Banner/SliderActivity/div_control_panel'))

WebUI.click(findTestObject('CMS/Banner/SliderActivity/div_slider'))

WebUI.click(findTestObject('CMS/Banner/DisplaySliderSection/button_editeng_banner'))

WebUI.click(findTestObject('CMS/Banner/TextOnSlide/div_editing_slide'))

WebUI.setText(findTestObject('CMS/Banner/TextOnSlide/div_text_box'), 'Test')

WebUI.click(findTestObject('CMS/Banner/TextOnSlide/button_save'))

WebUI.navigateToUrl(BaseLink.getUrlDef())

WebUI.verifyElementText(findTestObject('CMS/Banner/TextOnSlide/p_text_on_page'), 'Test')

