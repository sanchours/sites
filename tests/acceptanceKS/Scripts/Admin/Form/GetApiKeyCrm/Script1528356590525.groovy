/**
 * @description Интеграция с CRM
 * @step Переход на площадку CRM
 * @step Переход в настройки интеграции
 * @step Получение ключа API
 * @step Переход в админку CMS
 * @step Установка модуля "CRM"
 * @step Переход в настройки модуля CRM
 * @step Выбор типа подключения по API
 * @step Устнановка значения в поле адрес площадки CRM
 * @step Установка ключа API
 * @step Сохранение
 * @step Переход на форму
 * @step Переход в настройки формы
 * @step Переход в настройки связи с CRM
 * @step Переход установка галочки "Отправлять данные  в CRM"
 * @step Переход в параметр "Список полей"
 * @step Настройка связей полей формы и CRM
 * @step Сохранение изменений
 * @step Отправка формы 
 * @step Проверка в CRM данных с формы
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



