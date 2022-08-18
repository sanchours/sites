import React, {useEffect, useState} from 'react';
import _ from 'lodash'
import classNames from 'classnames'
import UilScenery from '@iconscout/react-unicons/icons/uil-scenery';
import {Modal} from "antd";
import {connect} from 'dva'
import TestPanel from "../TestPanel/Index";
import * as sk from "../../../services/_sk/api";
import styles from './Index.less'

const Content = ({ moduleData, selectedItems, setSelectedItems }) => {

  const handleClick = (item) => (event) => {

    // Зажата клавиша CTRL
    if (event.ctrlKey) {

      const indexInSelectedItems = _.findIndex(selectedItems, (elem) => {
        return elem.webPathShort === item.webPathShort
      });

      if (indexInSelectedItems !== -1) {
        // вычитаем элемент
        setSelectedItems(_.differenceWith(selectedItems, [item], _.isEqual));
      } else {
        // добавляем элемент
        setSelectedItems(_.unionWith(selectedItems, [item], _.isEqual));
      }

    } else {
      // Выбираем один элемент
      setSelectedItems([item]);
    }

  };

  const image = (val) => {
    if (val.preview) {
      return <img src={val.preview} alt="" />
    }
    return (
      <div title="no image" className="sk-no-image">
        <UilScenery size="20" />
      </div>
    )
  };

  const files = moduleData.getIn(['params', 'files'])
    ? moduleData.getIn(['params', 'files']).toJS()
    : [];

  return files.length
    ? (
      <div className="list-files">
        {
          moduleData.getIn(['params', 'files']).toJS().map(val => {

            const isSelected = (
              _.findIndex(selectedItems, el => el.webPathShort === val.webPathShort) !== -1
            );

            return (
              <div
                key={val.name}
                className={
                  classNames("list-files__item", {
                    "list-files__item--selected": isSelected
                  })
                }
                onClick={handleClick(val)}
              >
                {image(val)}
                <div className="list-files__title" title={val.name}>
                  {val.name}
                </div>
              </div>
            )
          })
        }
      </div>
    ) : null;
};

const Files = ({ moduleData, dispatch }) => {

  const [selectedItems, setSelectedItems] = useState([]);

  useEffect(() => {

    const loadedFiles = moduleData.getIn(['params', 'loadedFiles'])
      ? moduleData.getIn(['params', 'loadedFiles']).toJS()
      : []
    ;

    // Если есть загруженные записи, то выделим их
    if ( loadedFiles.length ){

      const files = moduleData.getIn(['params', 'files'])
        ? moduleData.getIn(['params', 'files']).toJS()
        : [];

      const selectedFiles = files.filter(fileItem => loadedFiles.includes(fileItem.name));

      setSelectedItems(selectedFiles);

    }

  }, moduleData.getIn(['params', 'loadedFiles'], []));

  const handleClickOnButton = (configButton) => () => {

    const {state = ''} = configButton;

    switch (state) {

      case 'delete':

        // если не выбрано - выдать ошибку
        if ( !selectedItems.length ){
          sk.error(moduleData.getIn(['init','lang', 'fileBrowserNoSelection']));
          return false;
        }

        // задание текста для подтверждения
        let rowText;

        if ( selectedItems.length === 1 ){
          rowText = _.head(selectedItems).name || '';

          if ( rowText ){
            rowText = `"${rowText}"`;
          } else {
            rowText = moduleData.getIn(['init', 'lang', 'delRowNoName']);
          }

        } else {

          rowText = `${selectedItems.length.toString()} ${moduleData.getIn(['init', 'lang', 'delCntItems'])}`;

        }

        sk.showModal(sk.dict('delRowHeader'), <div dangerouslySetInnerHTML={{ __html: sk.dict('delRow').replace('{0}', rowText) }} />, () => {

          const delItems = selectedItems.map(item => item.name);

          const dataPack = moduleData.getIn(['params', 'serviceData']).toJS();

          const componentData = {
            cmd: 'delete',
            delItems
          };

          dispatch({
            type: 'skGlobal/deleteFileItems',
            payload: {
              path: moduleData.get('path'),
              data: {
                ...dataPack,
                ...componentData
              }
            }
          });

          // чистим state
          setSelectedItems([]);

        });

        break;

      case 'copy_filelink':

        if (selectedItems.length === 0){
          sk.error(moduleData.getIn(['init','lang','chooseFile']));
          return false;
        }

        const items = selectedItems.map((item) =>
          <p key={item.name}>{item.webPathShort}</p>
        );

        Modal.info({
          title: moduleData.getIn(['init', 'lang', 'showFilesLink']),
          content: (
            <div>
              {items}
            </div>
          ),
          //
          onOk() {
          },
        });

        break;

      default:

        dispatch({
          type: 'skGlobal/filesAddForm',
          payload: {
            path: moduleData.get('path')
          }
        });

    }

    return true;

  };

  return (
    <TestPanel
      title={moduleData.getIn(['params', 'panelTitle'])}
      buttonsData={moduleData.getIn(['params', 'dockedItems', 'left'])}
      handleClickOnButton={handleClickOnButton}
    >
      <Content moduleData={moduleData} selectedItems={selectedItems} setSelectedItems={setSelectedItems} />
    </TestPanel>
  );
};

export default connect(() => {})(Files);
