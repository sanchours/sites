import React from 'react';
import {AutoComplete, Input} from "antd";
import UilSearch from '@iconscout/react-unicons/icons/uil-search';
import styles from "./style.less";

export default ({dispatch, moduleData}) => {

  let timer = 0;
  let searchValue = '';
  const timerDelay = 700;

  const handleOnSelect = (value) => {
    window.location.replace(value);
  };

  const handleOnSearch = (value) => {
    searchValue = value;
    if (timer === 0) {
        timer = setTimeout(() => {
          dispatch({
            type: 'skGlobal/search',
            payload: {
              path: 'out.header.search',
              query: searchValue
            }
          });
          timer = 0;
        }, timerDelay);
    }

  };

  if (!moduleData)
    return null;

  const langValues = moduleData.getIn(['init', 'lang']);

  const items = moduleData.getIn(['params', 'items']) || [];

  const dataSource = items.map(val => {
    return {
      value: val.get('url'),
      text: val.get('title')
    }
  });

  return (
    <AutoComplete
      className="nav-top_search"
      dataSource={dataSource}
      onSelect={handleOnSelect}
      onSearch={handleOnSearch}
      placeholder={langValues.get('searchSubText')}
    >
      <Input
        suffix={
          <UilSearch size="20" className="" />
        }
      />
    </AutoComplete>
  );
}
