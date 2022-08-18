import React from 'react';
import { Tooltip } from 'antd';
import _ from 'lodash'
import Icon from "../../../../Icon/Index";

const statusNotTranslated = 0;
const statusTranslated = 1;
const statusInProcess = 2;

const handleClick = (props) => (e) => {
  // останавливаем дальнейшее всплытие события
  e.stopPropagation();

  const {record, mainContainerData, dispatch} = props;

  // record = {group: ".", id: "1508", key: "1508", name: "layout", parent: "78", show_val: "", title: "", value: ""}

  const status = parseInt(record.status);
  let newStatus;

  /**
   * не переведен -> переведен
   * переведен -> в процессе
   * в процессе -> переведен
   *
   * в статус не перевед не возвращаемя, т.к. за перевод уже взялись
   * единственный способ перейти в "не переведен" - удалить параметр
   */
  switch ( status ) {
    case statusTranslated:
      newStatus = statusInProcess;
      break;
    case statusInProcess:
      newStatus = statusTranslated;
      break;

    case statusNotTranslated:
    default :
      newStatus = statusTranslated;
  }

  dispatch({
    type: 'skGlobal/handleCustomButtonInParameters',
    payload: {
      dispatchCmd: 'save',
      path: mainContainerData.get('path'),
      data: _.merge(
        {
          cmd: 'save',
          data: {
            ...record,
            status: newStatus
          }
        },
        mainContainerData.getIn(['params', 'serviceData']).toJS()
      )
    }
  });


};

export default (props) => {

  const {record} = props;

  let iconAlias;

  switch ( parseInt(record.status) ) {
    case statusTranslated:
      iconAlias = 'icon-saved';
      break;
    case statusInProcess:
      iconAlias = 'icon-edit';
      break;

    case statusNotTranslated:
    default :
      iconAlias = 'icon-stop';
  }

  return (
    <Tooltip
      placement="bottomRight"
    >
      <span>
        <Icon
          alias={iconAlias}
          addProps={{onClick: handleClick(props)}}
        />
      </span>
    </Tooltip>
  );
};
