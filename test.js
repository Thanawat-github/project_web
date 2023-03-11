
    var data = [
      {"Name":"Test1","dec":"123"},
      {"Name":"user","dec":"456"},
      {"Name":"Ropbert","dec":"789"},
      {"Name":"hitesh","dec":"101112"}
      ]

    var eye =[
    {"_x":363.6536543369293,"_y":306.49599438905716},
    {"_x":374.64060562849045,"_y":301.68148016929626},
    {"_x":386.37036287784576,"_y":303.0888205766678},
    {"_x":393.51227420568466,"_y":310.16365790367126},
    {"_x":384.8355562090874,"_y":313.0760455131531},
    {"_x":372.86268424987793,"_y":310.4075014591217}]
    //console.log(data)
    //delete data.result[1]
    //data.splice(2,1); 
    // for (let [i, user] of data.entries()) {
    //   if (user.FirstName === "user") {
    //     data.splice(i, 1); // Tim is now removed from "users"
    //     break;
    //   }
    //   console.log(i)
    // }
    // for (let [i, user] of data.entries()) {
    //     if (user.Name === "user") {
    //       data[i]['img']='kkkkkk';
    //       break;
    //     }
    //     //console.log(i)
    // }

    // var filtered = data.filter(a => a.Name == "user");
    // console.log(filtered);

    // data.filter(function(item,index){
    //   if(item.Name == "user"){
    //     console.log(data[index].dec) ;
    //     return false;
    //   }  
    // });

   
    var leftx = eye.map(eye => eye._x).reduce((acc, amount) => acc + amount);
console.log(leftx)
var lefty = eye.map(eye => eye._y).reduce((acc, amount) => acc + amount);
console.log(lefty)

    // var yyy = data[0].dec.includes('123');
    // console.log(yyy) ;
    // if(yyy){
    //   console.log('yes') ;
    // }
    
    

    console.log("**********")
    //console.log(data)
    // count=0
    // for( i=count-1;i>=0;i--){
    //   console.log(i)
    // }
    // console.log(i)
    // console.log(count)
