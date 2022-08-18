/**
 * @description Галочка "Показывать на внутренних страницах"
 * @step Переход в панель управления
 * @step Переход в модуль "Слайдер"
 * @step Переход в интерфейс редактирования сладера
 * @step Нажатие на кнопку "Настройки слайдера"
 * @step Выбор раздела для показа
 * @step Установка галочки "Показывать на внутренних страницах"
 * @step Переход на внутреннюю для выбранной для показа страницу сайта
 * @step Проверка наличия слайдера
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

WebUI.click(findTestObject('CMS/Banner/SliderActivity/div_control_panel'))

WebUI.click(findTestObject('CMS/Banner/SliderActivity/div_slider'))

WebUI.click(findTestObject('CMS/Banner/DisplaySliderSection/button_editeng_banner'))

WebUI.click(findTestObject('CMS/Banner/DisplaySliderSection/button_banner_settings'))

WebUI.click(findTestObject('CMS/Banner/DisplaySliderSection/div_display_sections_list'))

WebUI.click(findTestObject('CMS/Banner/DisplaySliderSection/li_display_section_list_item'))

WebUI.click(findTestObject('CMS/Banner/CheckboxShowOnInsidePage/checkbox_show_on_inside_page'))

WebUI.click(findTestObject('CMS/Banner/DisplaySliderSection/button_save_banner'))

WebUI.click(findTestObject('CMS/Banner/DisplaySliderSection/div_sections'))

WebUI.click(findTestObject('CMS/Banner/DisplaySliderSection/div_left_menu'))

WebUI.click(findTestObject('CMS/Banner/DisplaySliderSection/div_goods_group'))

WebUI.click(findTestObject('CMS/Banner/CheckboxShowOnInsidePage/div_inside_section'))

WebUI.delay(5)

WebUI.click(findTestObject('CMS/Banner/DisplaySliderSection/a_page_link'))

WebUI.switchToWindowIndex(1)

WebUI.verifyElementPresent(findTestObject('CMS/Banner/SliderActivity/div_slider_on_page'), 0)

