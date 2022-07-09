<?php
include "../dbconfig.php";

if(isset($_POST['stocknamefilter']) && isset($_POST['daterangefilter']))
{
    $stocknamefilter =  trim(isset($_POST["stocknamefilter"]) ? $_POST["stocknamefilter"] : "");
    $daterangefilter =  isset($_POST["daterangefilter"]) ? $_POST["daterangefilter"] : "";
	$serialnumber = 1;

    $middlestring = "-";


    if(strpos($daterangefilter, $middlestring) !== false)
    {
        $splitingdate = explode("-", $daterangefilter);
        $fromdate = $splitingdate[0];
        $todate = $splitingdate[1];

        $fromdate = trim(str_replace ("/", "-", $fromdate));

        $todate = trim(str_replace ("/", "-", $todate));

        $fromdate = date("Y-m-d", strtotime($fromdate));

        $todate = date("Y-m-d", strtotime($todate));

        try
        {

			$stocklistres = $conn->prepare("SELECT * from table_stocks WHERE stock_name = :stock_name and stock_date BETWEEN :fromdate AND :todate ORDER BY stock_date ASC");
            $stocklistres->bindParam(':stock_name', $stocknamefilter);
            $stocklistres->bindParam(':fromdate', $fromdate);
            $stocklistres->bindParam(':todate', $todate);
            $stocklistres->execute();

            if ($stocklistres->rowcount() > 0)
            {
                $stocklistresult = $stocklistres->fetchAll();
                $stocklisttitle = "Filtered Stock List";
				
				$firstitemcheck = 0;
				$minstockprice =0;
				
				foreach ($stocklistresult as $stocklistrow1000)
				{
					$loweststockprice11 = $stocklistrow1000['stock_price'];
					$loweststockpricedate11  = $stocklistrow1000['stock_date'];
					$loweststockpriceid11  = $stocklistrow1000['id'];
					
					if($firstitemcheck == 0)
					{
						$firstitemprice = $loweststockprice11;
						$firstitempricedate = $loweststockpricedate11;
						$firstitempriceid = $loweststockpriceid11;
						
						$minstockpricedate = $firstitempricedate;
							
						$minstockpriceid = $firstitempriceid;
						
						$minstockprice = $firstitemprice;
					}
					else
					{
						$seconditemprice = $loweststockprice11;
						$seconditempricedate = $loweststockpricedate11;
						$seconditempriceid = $loweststockpriceid11;
						
						$difference = $seconditemprice - $firstitemprice;
						
						if($difference > $minstockdifference)
						{
							$minstockdifference = $difference;
							
							$minstockpricedate = $firstitempricedate;
							
							$minstockpriceid = $firstitempriceid;
							
							$minstockprice = $firstitemprice;
							
						}
						
						$firstitemprice = $seconditemprice;
						$firstitempricedate = $seconditempricedate;
						$firstitempriceid = $seconditempriceid;
					}
					
					$firstitemcheck++;
				}
				
				$stocklistres10 = $conn->prepare("SELECT * from table_stocks WHERE stock_name = :stock_name and stock_price < :stock_price and stock_date < :stock_date and stock_date BETWEEN :fromdate AND :todate ORDER BY stock_price ASC LIMIT 1");
				$stocklistres10->bindParam(':stock_name', $stocknamefilter);
				$stocklistres10->bindParam(':stock_price', $minstockprice);
				$stocklistres10->bindParam(':stock_date', $minstockpricedate);
				$stocklistres10->bindParam(':fromdate', $fromdate);
				$stocklistres10->bindParam(':todate', $todate);
				$stocklistres10->execute();
					
				if ($stocklistres10->rowcount() > 0)
				{
					$stocklistresrow10 = $stocklistres10->fetch();
                    $loweststockprice = $stocklistresrow10['stock_price'];
					$loweststockpricedate  = $stocklistresrow10['stock_date'];
					$loweststockpriceid  = $stocklistresrow10['id'];
					
					

					$stocklistres20 = $conn->prepare("SELECT * from table_stocks WHERE stock_name = :stock_name and stock_date > :stock_date AND stock_price > :stock_price AND stock_date BETWEEN :fromdate AND :todate ORDER BY stock_price DESC LIMIT 1");
					$stocklistres20->bindParam(':stock_name', $stocknamefilter);
					$stocklistres20->bindParam(':stock_date', $loweststockpricedate);
					$stocklistres20->bindParam(':stock_price', $loweststockprice);
					$stocklistres20->bindParam(':fromdate', $fromdate);
					$stocklistres20->bindParam(':todate', $todate);
					$stocklistres20->execute();
					
					if ($stocklistres20->rowcount() > 0)
					{
						$stocklistresrow20 = $stocklistres20->fetch();
						$higheststockprice  = $stocklistresrow20['stock_price'];
						$higheststockpricedate  = $stocklistresrow20['stock_date'];
						$higheststockpriceid  = $stocklistresrow20['id'];
						
						$max_value = $higheststockpriceid;
						$min_value = $loweststockpriceid;
						
						

						foreach ($stocklistresult as $stocklistrow)
						{
							$finalstockdate = $stocklistrow['stock_date'];
							$finalstockdate = date("d-m-Y", strtotime($finalstockdate));
							$stocklistprice = $stocklistrow['stock_price'];

							

							if($stocklistrow['id'] == $min_value)
							{
								$classname = "greenclass";
								$blinktext = "Best date to buy";
							}
							else if($stocklistrow['id'] == $max_value)
							{
								$classname = "redclass";
								$blinktext = "Best date to sell";
							}
							else
							{
								 $classname = "";
								 $blinktext = "";
							}

							$output = $output."<tr>
										<th scope='row'>".$serialnumber."</th>
										<td>".$finalstockdate."</td>
										<td>".$stocklistrow['stock_name']."</td>
										<td>".$stocklistrow['stock_price']."</td>
										
										<td class='$classname'>".$blinktext."</td>
										</tr>";

							$serialnumber++;
						}

						echo json_encode(array("status" => "1","message" => "Record fetched", "record"=>$output,"stocklisttitle"=>$stocklisttitle));
					}
					else
					{
						$stocklistres30 = $conn->prepare("SELECT * from table_stocks WHERE stock_name = :stock_name and stock_price = :stock_price AND stock_date > :stock_date AND stock_date BETWEEN :fromdate AND :todate ORDER BY stock_price desc LIMIT 1");
						$stocklistres30->bindParam(':stock_name', $stocknamefilter);
						$stocklistres30->bindParam(':stock_price', $loweststockprice);
						$stocklistres30->bindParam(':stock_date', $loweststockpricedate);
						$stocklistres30->bindParam(':fromdate', $fromdate);
						$stocklistres30->bindParam(':todate', $todate);
						$stocklistres30->execute();

						if ($stocklistres30->rowcount() > 0)
						{
							$stocklistresrow30 = $stocklistres30->fetch();
							$secondlowestprice  = $stocklistresrow30['stock_price'];
							$secondlowestpricedate  = $stocklistresrow30['stock_date'];
							$secondlowestpriceid  = $stocklistresrow30['id'];
							
							$max_value = $secondlowestpriceid;
							$min_value = $loweststockpriceid;
							
							

							foreach ($stocklistresult as $stocklistrow)
							{
								$finalstockdate = $stocklistrow['stock_date'];
								$finalstockdate = date("d-m-Y", strtotime($finalstockdate));
								$stocklistprice = $stocklistrow['stock_price'];

								if($stocklistrow['id'] == $min_value)
								{
									$classname = "greenclass";
									 $blinktext = "Best date to buy";
								}
								else if($stocklistrow['id'] == $max_value)
								{
									$classname = "redclass";
									 $blinktext = "Best date to sell without profit";
								}
								else
								{
									 $classname = "";
									 $blinktext = "";
								}

								$output = $output."<tr>
											<th scope='row'>".$serialnumber."</th>
											<td>".$finalstockdate."</td>
											<td>".$stocklistrow['stock_name']."</td>
											<td>".$stocklistrow['stock_price']."</td>
											
											<td class='$classname'>".$blinktext."</td>
											</tr>";

								$serialnumber++;
							}

							echo json_encode(array("status" => "1","message" => "Record fetched", "record"=>$output,"stocklisttitle"=>$stocklisttitle));
							
						}
						else
						{
							$stocklistres40 = $conn->prepare("SELECT * from table_stocks WHERE stock_name = :stock_name and stock_price < :stock_price AND stock_date > :stock_date AND stock_date BETWEEN :fromdate AND :todate ORDER BY stock_price desc LIMIT 1");
							$stocklistres40->bindParam(':stock_name', $stocknamefilter);
							$stocklistres40->bindParam(':stock_price', $loweststockprice);
							$stocklistres40->bindParam(':stock_date', $loweststockpricedate);
							$stocklistres40->bindParam(':fromdate', $fromdate);
							$stocklistres40->bindParam(':todate', $todate);
							$stocklistres40->execute();

							if ($stocklistres40->rowcount() > 0)
							{
								$stocklistresrow40 = $stocklistres40->fetch();
								$thirdlowestprice  = $stocklistresrow40['stock_price'];
								$thirdlowestpricedate  = $stocklistresrow40['stock_date'];
								$thirdlowestpriceid  = $stocklistresrow40['id'];
								
								$max_value = $thirdlowestpriceid;
								$min_value = $loweststockpriceid;
								
								

								foreach ($stocklistresult as $stocklistrow)
								{
									$finalstockdate = $stocklistrow['stock_date'];
									$finalstockdate = date("d-m-Y", strtotime($finalstockdate));
									$stocklistprice = $stocklistrow['stock_price'];

									if($stocklistrow['id'] == $min_value)
									{
										$classname = "greenclass";
										 $blinktext = "Best date to buy to minimize the loss";
									}
									else if($stocklistrow['id'] == $max_value)
									{
										$classname = "redclass";
										 $blinktext = "Best date to sell to minimize the loss";
									}
									else
									{
										 $classname = "";
										 $blinktext = "";
									}

									$output = $output."<tr>
												<th scope='row'>".$serialnumber."</th>
												<td>".$finalstockdate."</td>
												<td>".$stocklistrow['stock_name']."</td>
												<td>".$stocklistrow['stock_price']."</td>
												
												<td class='$classname'>".$blinktext."</td>
												</tr>";

									$serialnumber++;
								}

								echo json_encode(array("status" => "1","message" => "Record fetched", "record"=>$output,"stocklisttitle"=>$stocklisttitle));
								
							}
							else
							{
								
								$stocklisttitle = "Please add more dates for better results for buying and selling suggestions";
								foreach ($stocklistresult as $stocklistrow)
								{
									$finalstockdate = $stocklistrow['stock_date'];
									$finalstockdate = date("d-m-Y", strtotime($finalstockdate));
									$stocklistprice = $stocklistrow['stock_price'];

									$output = $output."<tr>
												<th scope='row'>".$serialnumber."</th>
												<td>".$finalstockdate."</td>
												<td>".$stocklistrow['stock_name']."</td>
												<td>".$stocklistrow['stock_price']."</td>
												</tr>";

									$serialnumber++;
								}

								echo json_encode(array("status" => "2","message" => "Record fetched", "record"=>$output,"stocklisttitle"=>$stocklisttitle));
								
							}
						}
					}
				}
				
				else
				{
					$stocklistres50 = $conn->prepare("SELECT * from table_stocks WHERE stock_name = :stock_name and id = :id AND stock_date BETWEEN :fromdate AND :todate ORDER BY stock_price ASC LIMIT 1");
					$stocklistres50->bindParam(':stock_name', $stocknamefilter);
					$stocklistres50->bindParam(':id', $minstockpriceid);
					$stocklistres50->bindParam(':fromdate', $fromdate);
					$stocklistres50->bindParam(':todate', $todate);
					$stocklistres50->execute();
						
					if ($stocklistres50->rowcount() > 0)
					{
						$stocklistresrow50 = $stocklistres50->fetch();
						$loweststockprice = $stocklistresrow50['stock_price'];
						$loweststockpricedate  = $stocklistresrow50['stock_date'];
						$loweststockpriceid  = $stocklistresrow50['id'];
						
						

						$stocklistres60 = $conn->prepare("SELECT * from table_stocks WHERE stock_name = :stock_name and stock_date > :stock_date AND stock_price > :stock_price AND stock_date BETWEEN :fromdate AND :todate ORDER BY stock_price DESC LIMIT 1");
						$stocklistres60->bindParam(':stock_name', $stocknamefilter);
						$stocklistres60->bindParam(':stock_date', $loweststockpricedate);
						$stocklistres60->bindParam(':stock_price', $loweststockprice);
						$stocklistres60->bindParam(':fromdate', $fromdate);
						$stocklistres60->bindParam(':todate', $todate);
						$stocklistres60->execute();
						
						if ($stocklistres60->rowcount() > 0)
						{
							$stocklistresrow60 = $stocklistres60->fetch();
							$higheststockprice  = $stocklistresrow60['stock_price'];
							$higheststockpricedate  = $stocklistresrow60['stock_date'];
							$higheststockpriceid  = $stocklistresrow60['id'];
							
							$max_value = $higheststockpriceid;
							$min_value = $loweststockpriceid;
							
							

							foreach ($stocklistresult as $stocklistrow)
							{
								$finalstockdate = $stocklistrow['stock_date'];
								$finalstockdate = date("d-m-Y", strtotime($finalstockdate));
								$stocklistprice = $stocklistrow['stock_price'];

								

								if($stocklistrow['id'] == $min_value)
								{
									$classname = "greenclass";
									$blinktext = "Best date to buy";
								}
								else if($stocklistrow['id'] == $max_value)
								{
									$classname = "redclass";
									$blinktext = "Best date to sell";
								}
								else
								{
									 $classname = "";
									 $blinktext = "";
								}

								$output = $output."<tr>
											<th scope='row'>".$serialnumber."</th>
											<td>".$finalstockdate."</td>
											<td>".$stocklistrow['stock_name']."</td>
											<td>".$stocklistrow['stock_price']."</td>
											
											<td class='$classname'>".$blinktext."</td>
											</tr>";

								$serialnumber++;
							}

							echo json_encode(array("status" => "1","message" => "Record fetched", "record"=>$output,"stocklisttitle"=>$stocklisttitle));
						}
						else
						{
							
							
							
							$stocklistres70 = $conn->prepare("SELECT * from table_stocks WHERE stock_name = :stock_name and stock_price = :stock_price AND stock_date > :stock_date AND stock_date BETWEEN :fromdate AND :todate ORDER BY stock_price desc LIMIT 1");
							$stocklistres70->bindParam(':stock_name', $stocknamefilter);
							$stocklistres70->bindParam(':stock_price', $loweststockprice);
							$stocklistres70->bindParam(':stock_date', $loweststockpricedate);
							$stocklistres70->bindParam(':fromdate', $fromdate);
							$stocklistres70->bindParam(':todate', $todate);
							$stocklistres70->execute();

							if ($stocklistres70->rowcount() > 0)
							{
								$stocklistresrow70 = $stocklistres70->fetch();
								$thirdlowestprice  = $stocklistresrow70['stock_price'];
								$thirdlowestpricedate  = $stocklistresrow70['stock_date'];
								$thirdlowestpriceid  = $stocklistresrow70['id'];
								
								$max_value = $thirdlowestpriceid;
								$min_value = $loweststockpriceid;
								
								

								foreach ($stocklistresult as $stocklistrow)
								{
									$finalstockdate = $stocklistrow['stock_date'];
									$finalstockdate = date("d-m-Y", strtotime($finalstockdate));
									$stocklistprice = $stocklistrow['stock_price'];

									if($stocklistrow['id'] == $min_value)
									{
										$classname = "greenclass";
										 $blinktext = "Best date to buy";
									}
									else if($stocklistrow['id'] == $max_value)
									{
										$classname = "redclass";
										 $blinktext = "Best date to sell without profit";
									}
									else
									{
										 $classname = "";
										 $blinktext = "";
									}

									$output = $output."<tr>
												<th scope='row'>".$serialnumber."</th>
												<td>".$finalstockdate."</td>
												<td>".$stocklistrow['stock_name']."</td>
												<td>".$stocklistrow['stock_price']."</td>
												
												<td class='$classname'>".$blinktext."</td>
												</tr>";

									$serialnumber++;
								}

								echo json_encode(array("status" => "1","message" => "Record fetched", "record"=>$output,"stocklisttitle"=>$stocklisttitle));
								
							}
							else
							{
								$stocklistres80 = $conn->prepare("SELECT * from table_stocks WHERE stock_name = :stock_name and stock_price < :stock_price AND stock_date > :stock_date AND stock_date BETWEEN :fromdate AND :todate ORDER BY stock_price desc LIMIT 1");
								$stocklistres80->bindParam(':stock_name', $stocknamefilter);
								$stocklistres80->bindParam(':stock_price', $loweststockprice);
								$stocklistres80->bindParam(':stock_date', $loweststockpricedate);
								$stocklistres80->bindParam(':fromdate', $fromdate);
								$stocklistres80->bindParam(':todate', $todate);
								$stocklistres80->execute();

								if ($stocklistres80->rowcount() > 0)
								{
									$stocklistresrow80 = $stocklistres80->fetch();
									$thirdlowestprice  = $stocklistresrow80['stock_price'];
									$thirdlowestpricedate  = $stocklistresrow80['stock_date'];
									$thirdlowestpriceid  = $stocklistresrow80['id'];
									
									$max_value = $thirdlowestpriceid;
									$min_value = $loweststockpriceid;
									
									

									foreach ($stocklistresult as $stocklistrow)
									{
										$finalstockdate = $stocklistrow['stock_date'];
										$finalstockdate = date("d-m-Y", strtotime($finalstockdate));
										$stocklistprice = $stocklistrow['stock_price'];

										if($stocklistrow['id'] == $min_value)
										{
											$classname = "greenclass";
											 $blinktext = "Best date to buy to minimize the loss";
										}
										else if($stocklistrow['id'] == $max_value)
										{
											$classname = "redclass";
											 $blinktext = "Best date to sell to minimize the loss";
										}
										else
										{
											 $classname = "";
											 $blinktext = "";
										}

										$output = $output."<tr>
													<th scope='row'>".$serialnumber."</th>
													<td>".$finalstockdate."</td>
													<td>".$stocklistrow['stock_name']."</td>
													<td>".$stocklistrow['stock_price']."</td>
													
													<td class='$classname'>".$blinktext."</td>
													</tr>";

										$serialnumber++;
									}

									echo json_encode(array("status" => "1","message" => "Record fetched", "record"=>$output,"stocklisttitle"=>$stocklisttitle));
									
								}
								else
								{
									$stocklisttitle = "Please add more dates for better results for buying and selling suggestions";
									foreach ($stocklistresult as $stocklistrow)
									{
										$finalstockdate = $stocklistrow['stock_date'];
										$finalstockdate = date("d-m-Y", strtotime($finalstockdate));
										$stocklistprice = $stocklistrow['stock_price'];

										$output = $output."<tr>
													<th scope='row'>".$serialnumber."</th>
													<td>".$finalstockdate."</td>
													<td>".$stocklistrow['stock_name']."</td>
													<td>".$stocklistrow['stock_price']."</td>
													</tr>";

										$serialnumber++;
									}

									echo json_encode(array("status" => "2","message" => "Record fetched", "record"=>$output,"stocklisttitle"=>$stocklisttitle));
									
								}
							}
						}
					}
				}
            }
            else
            {
                $stocklistres1 = $conn->prepare("SELECT * from table_stocks WHERE stock_name = :stock_name and stock_date <= :todate ORDER BY stock_date desc
				LIMIT 1 ");
                $stocklistres1->bindParam(':stock_name', $stocknamefilter);
                $stocklistres1->bindParam(':todate', $todate);
                $stocklistres1->execute();

                if ($stocklistres1->rowcount() > 0)
                {
                    $stocklistresult1 = $stocklistres1->fetchAll();
                    $stocklisttitle = "Stock List not available for the selected dates(Below list is suggested Stock List)";

                    foreach ($stocklistresult1 as $stocklistrow1)
                    {
                        $finalstockdate1 = $stocklistrow1['stock_date'];
                        $finalstockdate1 = date("d-m-Y", strtotime($finalstockdate1));

                        $output = $output."<tr>
                                    <th scope='row'>".$serialnumber."</th>
                                    <td>".$finalstockdate1."</td>
                                    <td>".$stocklistrow1['stock_name']."</td>
                                    <td>".$stocklistrow1['stock_price']."</td>
                                   
                                </tr>";
                        $serialnumber++;
                    }

                    echo json_encode(array("status" => "2","message" => "Record fetched", "record"=>$output,"stocklisttitle"=>$stocklisttitle));
                }
                else
                {
					$stocklistres1 = $conn->prepare("SELECT * from table_stocks WHERE stock_name = :stock_name and stock_date >= :todate ORDER BY stock_date ASC LIMIT 1 ");
					$stocklistres1->bindParam(':stock_name', $stocknamefilter);
					$stocklistres1->bindParam(':todate', $todate);
					$stocklistres1->execute();

					if ($stocklistres1->rowcount() > 0)
					{
						$stocklistresult1 = $stocklistres1->fetchAll();
						$stocklisttitle = "Stock List not available for the selected dates(Below list is suggested Stock List)";

						foreach ($stocklistresult1 as $stocklistrow1)
						{
							$finalstockdate1 = $stocklistrow1['stock_date'];
							$finalstockdate1 = date("d-m-Y", strtotime($finalstockdate1));

							$output = $output."<tr>
										<th scope='row'>".$serialnumber."</th>
										<td>".$finalstockdate1."</td>
										<td>".$stocklistrow1['stock_name']."</td>
										<td>".$stocklistrow1['stock_price']."</td>
									   
									</tr>";
							$serialnumber++;
						}

						echo json_encode(array("status" => "2","message" => "Record fetched", "record"=>$output,"stocklisttitle"=>$stocklisttitle));
						
					}
					else
					{
						$stocklisttitle = "Please upload the stock list before filter the record";
						$output = $output."<tr>
						<td colspan='5' class='text-center'>Stock List not Available. Please click the link to upload the file. <a href='upload_stock.php'>Upload Stock</a></td>
						</tr>";
						
						echo json_encode(array("status" => "2","message" => "Record fetched", "record"=>$output,"stocklisttitle"=>$stocklisttitle));
					}
                }
            }
        }
        catch(PDOException $e)
        {
            echo json_encode(array( "status" => "0","message" => "Sorry for the inconvenience. We will fix the problem soon. Please try after some time" ) );
        }
    }
    else
    {
        echo json_encode(array("status" => "0","message" => "Please Check From and To Date"));
    }
}
else
{
	echo json_encode(array("status" => "0","message" => "Please fill all the fields for better results"));
}
?>